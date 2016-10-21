using System;
using System.Collections.Generic;
using System.Net.Http.Headers;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.FrameworkEssentials.Services.Base;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Cache.Service;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiStorageService :  IApiStorageService
    {
        private readonly IStorageService _storageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiStorageService(IStorageService storageService, IApiConfigurationService apiConfigurationService)
        {
            _storageService = storageService;
            _apiConfigurationService = apiConfigurationService;
        }

        private readonly Dictionary<string, string> _modelDictionary = new Dictionary<string, string>();
        private readonly AsyncLock _asyncLock = new AsyncLock();

        private ApiRoamingEntity _apiRoamingEntity;
        private StorageServiceCache _storageServiceCache;
        private async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                try
                {
                    var json = await _storageService.GetRoamingTextFileAsync(GetApiRoamingFilePath());
                    _apiRoamingEntity = JsonConvert.DeserializeObject<ApiRoamingEntity>(json);

                    json = await _storageService.GetCachedTextFileAsync(GetApiStorageFilePath());
                    _storageServiceCache = JsonConvert.DeserializeObject<StorageServiceCache>(json);

                }
                catch (Exception)
                {
                    // omited as it can be a new installation
                }
                if (_apiRoamingEntity == null)
                    _apiRoamingEntity = new ApiRoamingEntity();

                if (_storageServiceCache == null)
                    _storageServiceCache = new StorageServiceCache();

                return true;
            }
        }


        private string GetApiCacheFilePath()
        {
            return _apiConfigurationService.GetFileName("api_cache.json");
        }

        private string GetApiRoamingFilePath()
        {
            return _apiConfigurationService.GetFileName("api_roaming.json");
        }

        private string GetApiStorageFilePath()
        {
            return _apiConfigurationService.GetFileName("api_storage_cache.json");
        }

        public Task<ApiRoamingEntity> GetApiRoamingEntity()
        {
            return ExecuteSafe(() => _apiRoamingEntity);

        }

        public Task<bool> SaveApiRoamingEntityAsync()
        {
            return ExecuteSafe(async () => await _storageService.SetRoamingTextFileAsync(GetApiRoamingFilePath(), JsonConvert.SerializeObject(_apiRoamingEntity)));
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
                _storageServiceCache = new StorageServiceCache();
                
                return true;
            });
        }

        public Task<CacheEntity<T>> GetCacheEntity<T>()
        {
            //get filename from instace, cache it, wat?
            throw new NotImplementedException();
        }

        public Task<bool> SaveCacheEntityAsync<T>()
        {
            throw new NotImplementedException();
        }

        public Task<bool> EraseCacheEntityAsync<T>()
        {
            throw new NotImplementedException();
        }

        public Task<CollectionCacheEntity<T>> GetCollectionCacheEntity<T>()
        {
            throw new NotImplementedException();
        }

        public Task<bool> SaveCollectionEntityAsync<T>()
        {
            throw new NotImplementedException();
        }

        public Task<bool> EraseCollectionEntityAsync<T>()
        {
            throw new NotImplementedException();
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
