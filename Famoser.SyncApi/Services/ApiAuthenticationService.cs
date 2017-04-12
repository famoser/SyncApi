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
            _apiUserAuthenticationService = apiUserAuthenticationService;
            _apiUserAuthenticationService.SetAuthenticationService(this);

            _apiDeviceAuthenticationService = apiDeviceAuthenticationService;
            _apiDeviceAuthenticationService.SetAuthenticationService(this);

            _apiInformation = apiConfigurationService.GetApiInformations();
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
                if (_lastRefresh < DateTime.Now - TimeSpan.FromSeconds(2))
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
            if (_apiCollectionRepositoryDictionary.ContainsKey(typeof(TCollection)))
            {
                var ss = _apiCollectionRepositoryDictionary[typeof(TCollection)] as IApiCollectionRepository<TCollection>;
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

        private readonly Dictionary<Type, IApiCollectionRepository<ICollectionModel>> _apiCollectionRepositoryDictionary = new Dictionary<Type, IApiCollectionRepository<ICollectionModel>>();
        public void RegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository) where TCollection : ICollectionModel
        {
            var castedRepo = repository as IApiCollectionRepository<ICollectionModel>;
            _apiCollectionRepositoryDictionary.Add(typeof(IApiCollectionRepository<TCollection>), castedRepo);
        }

        public Guid? TryGetDeviceId()
        {
            if (IsAuthenticated())
            {
                return _deviceModel?.GetId();
            }
            return null;
        }

        private readonly Dictionary<Type, List<IApiRepository<ISyncModel, ICollectionModel>>> _apiRepositoryDictionary = new Dictionary<Type, List<IApiRepository<ISyncModel, ICollectionModel>>>();
        public void RegisterRepository<TSyncModel, TCollection>(IApiRepository<TSyncModel, TCollection> repository) where TSyncModel : ISyncModel where TCollection : ICollectionModel
        {
            var castedRepository = (IApiRepository<ISyncModel, ICollectionModel>)repository;
            if (!_apiRepositoryDictionary.ContainsKey(typeof(TCollection)))
                _apiRepositoryDictionary.Add(typeof(TCollection), new List<IApiRepository<ISyncModel, ICollectionModel>>());

            _apiRepositoryDictionary[typeof(TCollection)].Add(castedRepository);
        }

        public void UnRegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository) where TCollection : ICollectionModel
        {
            _apiCollectionRepositoryDictionary.Remove(typeof(TCollection));
        }

        public void UnRegisterRepository<TSyncModel, TCollection>(IApiRepository<TSyncModel, TCollection> repository) where TSyncModel : ISyncModel where TCollection : ICollectionModel
        {
            _apiRepositoryDictionary.Remove(typeof(IApiRepository<TSyncModel, TCollection>));
        }

        public async Task CleanUpAfterUserRemoveAsync()
        {
            await _apiDeviceAuthenticationService.CleanUpDeviceAsync();

            _apiRoamingEntity = null;
            await CleanUpAfterDeviceRemoveAsync();
        }

        public async Task CleanUpAfterDeviceRemoveAsync()
        {
            foreach (var value in _apiCollectionRepositoryDictionary.Values)
            {
                await value.CleanUpAsync();
            }

            foreach (var value in _apiRepositoryDictionary.Values)
            {
                foreach (var apiRepository in value)
                {
                    await apiRepository.CleanUpAsync();
                }
            }

            //reset all auth
            _deviceModel = null;
            _lastRefresh = DateTime.MinValue;
        }

        public async Task CleanUpAfterCollectionRemoveAsync<TCollection>(TCollection collection) where TCollection : ICollectionModel
        {
            if (_apiRepositoryDictionary.ContainsKey(typeof(TCollection)))
            {
                var collRepo = _apiRepositoryDictionary[typeof(TCollection)];
                foreach (var apiRepo in collRepo)
                {
                    await apiRepo.RemoveAllFromCollectionAsync(collection);
                }
            }
        }
    }
}
