using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Interfaces;
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

        public ApiAuthenticationService(IApiUserAuthenticationService apiUserAuthenticationService, IApiDeviceAuthenticationService apiDeviceAuthenticationService, IApiConfigurationService apiConfigurationService)
        {
            _apiUserAuthenticationService = apiUserAuthenticationService;
            _apiDeviceAuthenticationService = apiDeviceAuthenticationService;

            _apiInformationEntity = apiConfigurationService.GetApiInformations();
        }
        
        public bool IsAuthenticated()
        {
            return _apiRoamingEntity?.AuthenticationState == AuthenticationState.Authenticated && _deviceModel?.GetAuthenticationState() == AuthenticationState.Authenticated;
        }

        private ApiRoamingEntity _apiRoamingEntity;
        private IDeviceModel _deviceModel;
        public async Task<bool> AuthenticateAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (_apiRoamingEntity == null || _deviceModel == null)
                {
                    _apiRoamingEntity = await _apiUserAuthenticationService.GetApiRoamingEntityAsync();
                    _deviceModel = await _apiDeviceAuthenticationService.GetAuthenticatedDeviceAsync(_apiRoamingEntity);
                }
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
                DeviceId = _deviceModel.GetId(),
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
            var mi = new ModelInformation
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
