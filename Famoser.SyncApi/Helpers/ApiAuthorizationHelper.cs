using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Helpers
{
    public class ApiAuthorizationHelper<TModel> : BaseHelper where TModel : ISyncModel
    {
        private readonly ApiClient<TModel> _apiClient;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;

        public ApiAuthorizationHelper(ApiClient<TModel> apiClient, IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
        {
            _apiClient = apiClient;
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
        }

        public Task<bool> InitializeUserAsync(Guid userId, Guid deviceId, ApiRoamingEntity apiRoamingEntity, ApiCacheEntity apiCacheEntity, ModelCacheEntity<TModel> modelCacheEntity)
        {
            return ExecuteSafe(async () =>
            {
                var userContent = JsonConvert.SerializeObject(await _apiConfigurationService.GetUserObjectAsync());
                var deviceContent = JsonConvert.SerializeObject(await _apiConfigurationService.GetDeviceObjectAsync());

                //add device & user
                var resp = await _apiClient.DoRequestAsync(new RequestEntity()
                {
                    UserEntities = new List<UserEntity>()
                    {
                        new UserEntity()
                        {
                            Id = userId,
                            OnlineAction = OnlineAction.Create,
                            Content = userContent,
                        }
                    },
                    DeviceEntities = new List<DeviceEntity>()
                    {
                        new DeviceEntity()
                        {
                            Id = deviceId,
                            UserId = userId,
                            OnlineAction = OnlineAction.Create,
                            Content = deviceContent,
                        }
                    }
                });


                var user = resp.UserEntities.FirstOrDefault(d => d.Id == userId);
                var device = resp.DeviceEntities.FirstOrDefault(d => d.Id == deviceId);
                if (user == null || device == null)
                    return false;

                apiRoamingEntity.UserId = userId;
                apiCacheEntity.DeviceId = deviceId;
                apiCacheEntity.UserEntity = new UserEntity()
                {
                    Content = userContent,
                    Id = userId,
                    VersionId = user.VersionId,
                };
                apiCacheEntity.DeviceEntity = new DeviceEntity()
                {
                    Content = deviceContent,
                    Id = deviceId,
                    VersionId = device.VersionId,
                };

                return !resp.RequestFailed;
            });
        }

        public Task<bool> InitializeDeviceAsync(Guid deviceId, ApiRoamingEntity apiRoamingEntity, ApiCacheEntity apiCacheEntity, ModelCacheEntity<TModel> modelCacheEntity)
        {
            return ExecuteSafe(async () =>
            {
                var deviceContent = JsonConvert.SerializeObject(await _apiConfigurationService.GetDeviceObjectAsync());

                //add device & user
                var resp = await _apiClient.DoRequestAsync(new RequestEntity()
                {
                    DeviceEntities = new List<DeviceEntity>()
                            {
                                new DeviceEntity()
                                {
                                    Id = deviceId,
                                    UserId = (await _apiStorageService.GetApiRoamingEntityAsync()).UserId,
                                    OnlineAction = OnlineAction.Create,
                                    Content = deviceContent,
                                }
                            }
                });

                if (resp.RequestFailed)
                    return false;

                var device = resp.DeviceEntities.FirstOrDefault(d => d.Id == deviceId);
                if (device == null)
                    return false;

                apiCacheEntity.DeviceEntity = new DeviceEntity()
                {
                    Content = deviceContent,
                    Id = deviceId,
                    VersionId = device.VersionId,
                };


                return !resp.RequestFailed;
            });

        }
    }
}
