using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel>
    where TModel : ISyncModel
    {
        private async Task<bool> InitializeRoamingAsync()
        {
            var userId = Guid.NewGuid();
            var deviceId = Guid.NewGuid();

            var userContent = JsonConvert.SerializeObject(await _apiConfiguration.GetUserObjectAsync());
            var deviceContent = JsonConvert.SerializeObject(await _apiConfiguration.GetDeviceObjectAsync());
            _apiClient = await GetApiClient();

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

            if (resp.RequestFailed)
                return false;

            var user = resp.UserEntities.FirstOrDefault(d => d.Id == userId);
            var device = resp.DeviceEntities.FirstOrDefault(d => d.Id == deviceId);
            if (user == null || device == null)
                return false;

            _apiRoamingEntity = new ApiRoamingEntity()
            {
                UserId = userId
            };
            _apiCacheEntity = new ApiCacheEntity()
            {
                DeviceId = deviceId,
                UserEntity = new UserEntity()
                {
                    Content = userContent,
                    Id = userId,
                    VersionId = user.VersionId,
                },
                DeviceEntity = new DeviceEntity()
                {
                    Content = deviceContent,
                    Id = deviceId,
                    VersionId = device.VersionId,
                }
            };


            return true;
        }

        private async Task<bool> InitializeCacheAsync()
        {
            var deviceId = Guid.NewGuid();
            _apiClient = await GetApiClient();

            //add device & user
            var resp = await _apiClient.DoRequestAsync(new RequestEntity()
            {
                DeviceEntities = new List<DeviceEntity>()
                            {
                                new DeviceEntity()
                                {
                                    Id = deviceId,
                                    UserId = _apiRoamingEntity.UserId,
                                    OnlineAction = OnlineAction.Create,
                                    Content = JsonConvert.SerializeObject(await _apiConfiguration.GetDeviceObjectAsync()),
                                }
                            }
            });

            return true;
        }
    }
}
