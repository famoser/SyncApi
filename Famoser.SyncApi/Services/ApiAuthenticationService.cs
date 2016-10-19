using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache.Entitites;
using Famoser.SyncApi.Storage.Roaming;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiAuthenticationService : IApiAuthenticationService
    {
        private readonly AsyncLock _asyncLock = new AsyncLock();
        private readonly IApiUserAuthenticationService _apiUserAuthenticationService;
        private readonly IApiDeviceAuthenticationService _apiDeviceAuthenticationService;
        private readonly ApiInformationEntity _apiInformationEntity;

        public ApiAuthenticationService(IApiUserAuthenticationService apiUserAuthenticationService, IApiDeviceAuthenticationService deviceAuthenticationService, IApiDeviceAuthenticationService apiDeviceAuthenticationService, IApiConfigurationService apiConfigurationService)
        {
            _apiUserAuthenticationService = apiUserAuthenticationService;
            _apiDeviceAuthenticationService = apiDeviceAuthenticationService;
            _apiDeviceAuthenticationService = deviceAuthenticationService;

            _apiInformationEntity = apiConfigurationService.GetApiInformations();
        }

        private bool _isAuthenticated;
        public bool IsAuthenticated()
        {
            return _isAuthenticated;
        }

        private ApiRoamingEntity _apiRoamingEntity;
        private Guid _deviceId;
        public async Task<bool> AuthenticateAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (_isAuthenticated)
                    return IsAuthenticated();

                _apiRoamingEntity = await _apiUserAuthenticationService.TryGetApiRoamingEntityAsync();
                var g = await _apiDeviceAuthenticationService.TryGetAuthenticatedDeviceIdAsync(_apiRoamingEntity);
                if (g.HasValue)
                {
                    _deviceId = g.Value;
                    _isAuthenticated = true;
                }
                else
                    _isAuthenticated = false;

                return IsAuthenticated();
            }
        }

        public bool AuthenticateRequest(BaseRequest request)
        {
            if (!IsAuthenticated())
                return false;

            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(_apiInformationEntity, _apiRoamingEntity);
            request.UserId = _apiRoamingEntity.UserId;
            request.DeviceId = _deviceId;

            return IsAuthenticated();
        }

        public bool FillModelInformation(ModelInformation info)
        {
            if (!IsAuthenticated())
                return false;

            info.UserId = _apiRoamingEntity.UserId;
            info.DeviceId = _deviceId;

            return IsAuthenticated();
        }
    }
}
