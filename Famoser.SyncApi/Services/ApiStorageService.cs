using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Cache;
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

        private async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                try
                {
                    var json = await _storageService.GetRoamingTextFileAsync(GetApiRoamingFilePath());
                    _apiRoamingEntity = JsonConvert.DeserializeObject<ApiRoamingEntity>(json);
                }
                catch (Exception)
                {
                    // omited as it can be a new installation
                }
                if (_apiRoamingEntity == null)
                    _apiRoamingEntity = new ApiRoamingEntity();

                return true;
            }
        }

        private string GetApiRoamingFilePath()
        {
            return _apiConfigurationService.GetFileName("api_roaming.json");
        }

        private string GetApiStorageFilePath()
        {
            return _apiConfigurationService.GetFileName("api_storage_cache.json");
        }

        public Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            return ExecuteSafe(() => _apiRoamingEntity);

        }

        public Task<bool> SaveApiRoamingEntityAsync()
        {
            return
                ExecuteSafe(async () => await _storageService.SetRoamingTextFileAsync(GetApiRoamingFilePath(),
                                JsonConvert.SerializeObject(_apiRoamingEntity)));
        }

        public Task<bool> EraseRoamingAndCacheAsync()
        {
            return ExecuteSafe(async () =>
            {
                await _storageService.DeleteRoamingFileAsync(GetApiRoamingFilePath());
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
        protected async Task<T> ExecuteSafe<T>(Func<Task<T>> func)
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

        protected async Task<T> ExecuteSafe<T>(Func<T> func)
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
