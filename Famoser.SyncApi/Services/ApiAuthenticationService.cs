using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Enums;
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

                _apiRoamingEntity = await _apiUserAuthenticationService.GetApiRoamingEntityAsync();
                var g = await _apiDeviceAuthenticationService.GetAuthenticatedDeviceIdAsync(_apiRoamingEntity);
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

        public T CreateRequest<T>(OnlineAction action) where T : BaseRequest, new()
        {
            if (!IsAuthenticated())
                return null;

            var request = new T
            {
                AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(_apiInformationEntity, _apiRoamingEntity),
                UserId = _apiRoamingEntity.UserId,
                DeviceId = _deviceId,
                OnlineAction = action
            };

            return request;
        }

        public T CreateRequest<T>(OnlineAction action, Type collectionType) where T : SyncEntityRequest, new()
        {
            var req = CreateRequest<T>(action);
            if (action == OnlineAction.SyncVersion && _collectionIdsDictionary.ContainsKey(collectionType))
            {
                foreach (var guid in _collectionIdsDictionary[collectionType])
                {
                    req.CollectionEntities.Add(new CollectionEntity()
                    {
                        Id = guid
                    });
                }
            }
            return req;
        }

        public ModelInformation CreateModelInformation()
        {
            if (!IsAuthenticated())
                return null;

            var mi = new ModelInformation
            {
                Id = Guid.NewGuid(),
                VersionId = Guid.NewGuid(),
                UserId = _apiRoamingEntity.UserId,
                DeviceId = _deviceId,
                CreateDateTime = DateTime.Now,
                PendingAction = PendingAction.Create
            };
            return mi;
        }

        private readonly Dictionary<Type, List<Guid>> _collectionIdsDictionary = new Dictionary<Type, List<Guid>>();
        public void OverwriteCollectionIds<TCollection>(List<Guid> id)
        {
            if (_collectionIdsDictionary.ContainsKey(typeof(TCollection)))
                _collectionIdsDictionary[typeof(TCollection)] = id;
            else
                _collectionIdsDictionary.Add(typeof(TCollection), id);
        }
    }
}
