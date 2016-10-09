using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Base;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiStorageService : BaseService, IApiStorageService
    {
        private readonly IStorageService _storageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiStorageService(IStorageService storageService, IApiConfigurationService apiConfigurationService)
        {
            _storageService = storageService;
            _apiConfigurationService = apiConfigurationService;
        }

        private ApiRoamingEntity _apiRoamingEntity;
        private ApiCacheEntity _apiCacheEntity;
        private readonly Dictionary<string, string> _modelDictionary = new Dictionary<string, string>();
        private readonly AsyncLock _asyncLock = new AsyncLock();
        public async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                try
                {
                    var json = await _storageService.GetRoamingTextFileAsync(GetApiRoamingFilePath());
                    _apiRoamingEntity = JsonConvert.DeserializeObject<ApiRoamingEntity>(json);

                    json = await _storageService.GetCachedTextFileAsync(GetApiCacheFilePath());
                    _apiCacheEntity = JsonConvert.DeserializeObject<ApiCacheEntity>(json);

                    foreach (var modelIdentifier in _apiCacheEntity.ModelIdentifiers)
                    {
                        _modelDictionary.Add(modelIdentifier, await _storageService.GetCachedTextFileAsync(modelIdentifier));
                    }
                }
                catch (Exception)
                {
                    // ignored: new installation perhaps, some exceptions may happen in storageservice
                }

                if (_apiRoamingEntity == null)
                {
                    //var apiClient = new ApiClient(_apiConfigurationService.GetApiUri(), userId);
                    var userId = Guid.NewGuid();
                    var deviceId = Guid.NewGuid();
                    _apiRoamingEntity = new ApiRoamingEntity {UserId = Guid.NewGuid()};
                    _apiCacheEntity = new ApiCacheEntity
                    {
                        DeviceId = Guid.NewGuid(),
                        DeviceEntity = new DeviceEntity()
                        {
                            Content = JsonConvert.SerializeObject(await _apiConfigurationService.GetDeviceObjectAsync()),
                            OnlineAction = OnlineAction.Create,
                            UserId = userId,
                            Id = deviceId
                        },
                        UserEntity = new UserEntity()
                        {
                            Content = JsonConvert.SerializeObject(await _apiConfigurationService.GetUserObjectAsync()),
                            OnlineAction = OnlineAction.Create,
                            Id = deviceId
                        }
                    };

                    await _storageService.SetRoamingTextFileAsync(GetApiRoamingFilePath(), JsonConvert.SerializeObject(_apiRoamingEntity));
                    await _storageService.SetCachedTextFileAsync(GetApiCacheFilePath(), JsonConvert.SerializeObject(_apiCacheEntity));
                }
                else if (_apiCacheEntity == null)
                {
                    var deviceId = Guid.NewGuid();
                    _apiCacheEntity = new ApiCacheEntity
                    {
                        DeviceId = Guid.NewGuid(),
                        DeviceEntity = new DeviceEntity()
                        {
                            Content = JsonConvert.SerializeObject(await _apiConfigurationService.GetDeviceObjectAsync()),
                            OnlineAction = OnlineAction.Create,
                            UserId = _apiRoamingEntity.UserId,
                            Id = deviceId
                        },
                        UserEntity = new UserEntity()
                        {
                            OnlineAction = OnlineAction.Read,
                            Id = _apiRoamingEntity.UserId
                        }
                    };

                    await _storageService.SetCachedTextFileAsync(GetApiCacheFilePath(), JsonConvert.SerializeObject(_apiCacheEntity));
                }
                //todo: sync apicache with auth
                return true;
            }
        }

        public ApiRoamingEntity GetApiRoamingEntity()
        {
            return _apiRoamingEntity;
        }

        public ApiCacheEntity GetApiCacheEntity()
        {
            return _apiCacheEntity;
        }

        public ModelCacheEntity<TModel> GetModelCache<TModel>(string identifier) where TModel : ISyncModel
        {
            if (_modelDictionary.ContainsKey(identifier))
                return JsonConvert.DeserializeObject<ModelCacheEntity<TModel>>(_modelDictionary[identifier]);
            return new ModelCacheEntity<TModel>();
        }

        public async Task SetModelCacheAsync<TModel>(string identifier, ModelCacheEntity<TModel> cache) where TModel : ISyncModel
        {
            _modelDictionary[identifier] = JsonConvert.SerializeObject(cache);
            if (!_apiCacheEntity.ModelIdentifiers.Contains(identifier))
            {
                _apiCacheEntity.ModelIdentifiers.Add(identifier);
                await _storageService.SetCachedTextFileAsync(GetApiCacheFilePath(), JsonConvert.SerializeObject(_apiCacheEntity));
            }
            await _storageService.SetCachedTextFileAsync(identifier, JsonConvert.SerializeObject(cache));
        }

        private string GetApiCacheFilePath()
        {
            return _apiConfigurationService.GetFileName("api_cache.json");
        }

        private string GetApiRoamingFilePath()
        {
            return _apiConfigurationService.GetFileName("api_roaming.json");
        }

    }
}
