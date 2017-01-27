using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Roaming;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Services
{
    public class ApiAuthenticationService : IApiAuthenticationService
    {
        private readonly AsyncLock _asyncLock = new AsyncLock();
        private readonly IApiUserAuthenticationService _apiUserAuthenticationService;
        private readonly IApiDeviceAuthenticationService _apiDeviceAuthenticationService;
        private readonly ApiInformation _apiInformation;

        public ApiAuthenticationService(IApiConfigurationService apiConfigurationService, IApiUserAuthenticationService apiUserAuthenticationService, IApiDeviceAuthenticationService apiDeviceAuthenticationService)
        {
            _apiInformation = apiConfigurationService.GetApiInformations();

            _apiUserAuthenticationService = apiUserAuthenticationService;
            _apiDeviceAuthenticationService = apiDeviceAuthenticationService;
        }

        private bool IsAuthenticated()
        {
            return IsInitialized() && _apiRoamingEntity.AuthenticationState == AuthenticationState.Authenticated && _deviceModel.GetAuthenticationState() == AuthenticationState.Authenticated;
        }

        private bool IsInitialized()
        {
            return _apiRoamingEntity != null && _deviceModel != null;
        }

        private DateTime _lastRefresh = DateTime.MinValue;
        private async Task ReInitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (_lastRefresh > DateTime.Now - TimeSpan.FromSeconds(2))
                {
                    _apiRoamingEntity = await _apiUserAuthenticationService.GetApiRoamingEntityAsync();
                    _deviceModel = await _apiDeviceAuthenticationService.GetDeviceAsync(_apiRoamingEntity);
                    _lastRefresh = DateTime.Now;
                }
            }
        }

        private ApiRoamingEntity _apiRoamingEntity;
        private IDeviceModel _deviceModel;
        public async Task<bool> IsAuthenticatedAsync()
        {
            if (IsAuthenticated())
                return true;
            await ReInitializeAsync();
            return IsAuthenticated();
        }

        public async Task<T> CreateRequestAsync<T>(int messageCount = 0) where T : BaseRequest, new()
        {
            if (!IsInitialized())
            {
                await ReInitializeAsync();
                if (!IsInitialized())
                    return null;
            }

            var request = new T
            {
                AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(_apiInformation, _apiRoamingEntity, messageCount),
                UserId = _apiRoamingEntity.UserId,
                DeviceId = _deviceModel.GetId(),
                ApplicationId = _apiInformation.ApplicationId
            };

            return request;
        }

        public async Task<T> CreateRequestAsync<T, TCollection>() where T : SyncEntityRequest, new() where TCollection : ICollectionModel
        {
            var req = await CreateRequestAsync<T>();
            if (_dictionary.ContainsKey(typeof(TCollection)))
            {
                var ss = _dictionary[typeof(TCollection)] as IApiCollectionRepository<TCollection>;
                if (ss != null)
                {
                    var colls = await ss.GetAllAsync();
                    foreach (var collection in colls)
                    {
                        req.CollectionEntities.Add(new CollectionEntity()
                        {
                            Id = collection.GetId(),
                            OnlineAction = OnlineAction.ConfirmAccess
                        });
                    }
                }
            }
            return req;
        }

        public async Task<CacheInformations> CreateModelInformationAsync()
        {
            if (!IsInitialized())
            {
                await ReInitializeAsync();
                if (!IsInitialized())
                    return null;
            }

            var mi = new CacheInformations
            {
                Id = Guid.NewGuid(),
                VersionId = Guid.NewGuid(),
                UserId = _apiRoamingEntity.UserId,
                DeviceId = _deviceModel.GetId(),
                CreateDateTime = DateTime.Now,
                PendingAction = PendingAction.Create
            };
            return mi;
        }

        private readonly Dictionary<Type, object> _dictionary = new Dictionary<Type, object>();
        public void RegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository) where TCollection : ICollectionModel
        {
            if (_dictionary.ContainsKey(typeof(TCollection)))
                _dictionary[typeof(TCollection)] = repository;
            else
                _dictionary.Add(typeof(TCollection), repository);
        }
    }
}
