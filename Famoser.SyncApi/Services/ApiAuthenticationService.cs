using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Clients;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Services.Interfaces;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiAuthenticationService : IApiAuthenticationService
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly AsyncLock _asyncLock = new AsyncLock();

        public ApiAuthenticationService(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
        }

        private async Task SyncAuthAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                var authApiClient = new AuthApiClient(_apiConfigurationService.GetApiUri());
                var apiCache = _apiStorageService.GetApiCacheEntity();
                //todo: create modelmanager for user & device, IoC register?
                var resp = await authApiClient.DoRequestAsync(new AuthRequestEntity()
                {
                    DeviceEntity = apiCache.DeviceEntity,
                    UserEntity = apiCache.UserEntity,
                    UserId = _apiStorageService.GetApiRoamingEntity().UserId
                });
                if (!resp.RequestFailed)
                {
                    if (resp.DeviceEntity != null)
                        apiCache.DeviceEntity = resp.DeviceEntity;
                    if (resp.UserEntity != null)
                        apiCache.UserEntity = resp.UserEntity;

                    if (resp.DeviceEntity != null || resp.UserEntity != null)
                    {
                        await _apiStorageService.SaveApiCacheEntityAsync();
                    }
                }
            }
        }

        public bool IsAuthenticated()
        {
            throw new NotImplementedException();
        }

        public async Task<bool> TryAuthenticationAsync()
        {
            throw new NotImplementedException();
        }

        public Guid GetUserId()
        {
            throw new NotImplementedException();
        }

        public Guid GetDeviceId()
        {
            throw new NotImplementedException();
        }
    }
}
