using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
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



            return true;
        }

        private async Task<bool> InitializeDeviceAsync()
        {
            var deviceId = Guid.NewGuid();
            
            var deviceContent = JsonConvert.SerializeObject(await _apiConfiguration.GetDeviceObjectAsync());
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
                                    Content = deviceContent,
                                }
                            }
            });

            if (resp.RequestFailed)
                return false;
            
            var device = resp.DeviceEntities.FirstOrDefault(d => d.Id == deviceId);
            if (device == null)
                return false;
            
            _apiCacheEntity.DeviceEntity = new DeviceEntity()
            {
                Content = deviceContent,
                Id = deviceId,
                VersionId = device.VersionId,
            };


            return true;
        }
    }
}
