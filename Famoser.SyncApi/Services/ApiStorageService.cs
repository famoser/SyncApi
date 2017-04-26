using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiStorageService : IApiStorageService
    {
        private readonly IStorageService _storageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiStorageService(IStorageService storageService, IApiConfigurationService apiConfigurationService)
        {
            _storageService = storageService;
            _apiConfigurationService = apiConfigurationService;
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ApiRoamingEntity _apiRoamingEntity;
        private bool _isAuthenticated = false;

        private async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (_isAuthenticated)
                {
                    return true;
                }

                _isAuthenticated = true;
                try
                {
                    var roamingFilePath = GetApiRoamingFilePath();
                    var json1 = await _storageService.GetRoamingTextFileAsync(roamingFilePath);
                    try
                    {
                        var json2 = await _storageService.GetCachedTextFileAsync(roamingFilePath);
                        if (json1 != json2)
                        {
                            //this is not good.
                            //scenario 1: roaming did not sync as expected, new installation has overwritten the roaming cache with new infos, user may lost all his data
                            var roaming1 = JsonConvert.DeserializeObject<ApiRoamingEntity>(json1);
                            var roaming2 = JsonConvert.DeserializeObject<ApiRoamingEntity>(json2);
                            if (roaming1.CreatedAt < roaming2.CreatedAt)
                            {
                                //hmmmmmm, this is unexpected. We will not modify the roaming storage, to not introduce more bugs
                                //we'll just pretend as everything is OK, maybe some other instance of this application can figure out whats going on
                                _apiRoamingEntity = roaming1;
                            }
                            else
                            {
                                //szenario 1 happened. too bad! At least we're smart enough to fix it (or not?). Override roaming storage with own storage
                                await _storageService.SetCachedTextFileAsync(roamingFilePath, json2);
                                _apiRoamingEntity = roaming2;
                            }
                        }
                    }
                    catch (Exception)
                    {
                        //this happens if installation on new device
                    }
                    _apiRoamingEntity = JsonConvert.DeserializeObject<ApiRoamingEntity>(json1);
                }
                catch (Exception)
                {
                    // this happens if its a new installation
                }
                if (_apiRoamingEntity == null)
                    _apiRoamingEntity = new ApiRoamingEntity();

                return true;
            }
        }

        private string _apiRoamingFilePathCache = null;
        private string GetApiRoamingFilePath()
        {
            return _apiRoamingFilePathCache ?? (_apiRoamingFilePathCache = _apiConfigurationService.GetFileName("api_roaming.json"));
        }

        private string _apiStorageFilePathCache = null;
        private string GetApiStorageFilePath()
        {
            return _apiStorageFilePathCache ?? (_apiStorageFilePathCache = _apiConfigurationService.GetFileName("api_storage_cache.json"));
        }

        public Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            return ExecuteSafeAsync(() => _apiRoamingEntity);

        }

        public Task<bool> SaveApiRoamingEntityAsync(ApiRoamingEntity entity)
        {
            return ExecuteSafeAsync(async () =>
            {
                var json = JsonConvert.SerializeObject(_apiRoamingEntity);
                var filePath = GetApiRoamingFilePath();
                var res = await _storageService.SetRoamingTextFileAsync(filePath, json);
                return res && await _storageService.SetCachedTextFileAsync(filePath, json);
            });
        }

        public Task<bool> EraseRoamingAndCacheAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                await _storageService.DeleteRoamingFileAsync(GetApiRoamingFilePath());
                await _storageService.DeleteCachedFileAsync(GetApiRoamingFilePath());
                await _storageService.DeleteCachedFileAsync(GetApiStorageFilePath());

                //invalidate userId
                _apiRoamingEntity.UserId = Guid.Empty;

                //new entities
                _apiRoamingEntity = new ApiRoamingEntity();

                return true;
            });
        }

        private readonly Dictionary<Type, string> _filenameCache = new Dictionary<Type, string>();
        private readonly Dictionary<string, object> _unserializeCache = new Dictionary<string, object>();

        public async Task<T> GetCacheEntityAsync<T>(string filename) where T : class, new()
        {
            if (!_filenameCache.ContainsKey(typeof(T)))
                _filenameCache.Add(typeof(T), filename);
            else
                _filenameCache[typeof(T)] = filename;

            if (!_unserializeCache.ContainsKey(filename))
            {
                try
                {
                    var json = await _storageService.GetCachedTextFileAsync(filename);
                    _unserializeCache.Add(filename, JsonConvert.DeserializeObject<T>(json));
                }
                catch
                {
                    //ignore because storage service can fail if file is not found
                }
                if (!_unserializeCache.ContainsKey(filename))
                    _unserializeCache.Add(filename, new T());
                else if (_unserializeCache[filename] == null)
                    _unserializeCache[filename] = new T();
            }
            return _unserializeCache[filename] as T;
        }

        public async Task<bool> SaveCacheEntityAsync<T>() where T : class, new()
        {
            if (!_filenameCache.ContainsKey(typeof(T)))
                return false;

            var key = _filenameCache[typeof(T)];
            return await _storageService.SetCachedTextFileAsync(key, JsonConvert.SerializeObject(_unserializeCache[key]));
        }

        public async Task<bool> EraseCacheEntityAsync<T>() where T : class, new()
        {
            if (!_filenameCache.ContainsKey(typeof(T)))
                return true; //no key anyways

            var key = _filenameCache[typeof(T)];
            _filenameCache.Remove(typeof(T));
            _unserializeCache.Remove(key);
            await _storageService.DeleteCachedFileAsync(key);
            return true;
        }

        public void SetExceptionLogger(IExceptionLogger logger)
        {
            _exceptionLogger = logger;
        }
        private IExceptionLogger _exceptionLogger;

        protected async Task<T> ExecuteSafeAsync<T>(Func<Task<T>> func)
            where T : new()
        {
            try
            {
                if (!await InitializeAsync())
                    return new T();

                return await func();
            }
            catch (Exception ex)
            {
                _exceptionLogger?.LogException(ex, this);
            }
            return default(T);
        }

        protected async Task<T> ExecuteSafeAsync<T>(Func<T> func)
            where T : new()
        {
            try
            {
                if (!await InitializeAsync())
                    return new T();

                return func();
            }
            catch (Exception ex)
            {
                _exceptionLogger?.LogException(ex, this);
            }
            return default(T);
        }
    }
}
