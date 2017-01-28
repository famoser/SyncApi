using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Communication.Response;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiDeviceRepository<TDevice> : PersistentRepository<TDevice>, IApiDeviceRepository<TDevice>
        where TDevice : class, IDeviceModel
    {
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiAuthenticationService _apiAuthenticationService;
        private readonly ApiClient _authApiClient;
        public ApiDeviceRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiTraceService traceService, IApiAuthenticationService apiAuthenticationService) :
            base(apiConfigurationService, apiStorageService, traceService, apiAuthenticationService)
        {
            _apiStorageService = apiStorageService;
            _apiAuthenticationService = apiAuthenticationService;
            _apiConfigurationService = apiConfigurationService;

            _authApiClient = GetAuthApiClient();
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CacheEntity != null)
                    return true;

                //need this from authenticationservice
                if (_apiRoamingEntity == null)
                    return false;

                CacheEntity = await _apiStorageService.GetCacheEntityAsync<CacheEntity<TDevice>>(GetModelCacheFilePath());
                if (CacheEntity.ModelInformation == null)
                {
                    CacheEntity.Model = await _apiConfigurationService.GetDeviceObjectAsync<TDevice>();
                    CacheEntity.ModelInformation = new CacheInformations()
                    {
                        Id = Guid.NewGuid(),
                        UserId = _apiRoamingEntity.UserId,
                        PendingAction = PendingAction.Create,
                        VersionId = Guid.NewGuid()
                    };
                    CacheEntity.Model.SetId(CacheEntity.ModelInformation.Id);
                    await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TDevice>>();
                }
                Manager.Set(CacheEntity.Model);

                return true;
            }
        }

        public override Task<bool> SyncAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                AuthorizationResponse resp = null;
                if (CacheEntity.ModelInformation.PendingAction == PendingAction.Delete)
                {
                    resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()
                        {
                            UserEntity = new UserEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Delete
                            }
                        }));
                }
                else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Create)
                {
                    resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()
                        {
                            DeviceEntity = new DeviceEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Create,
                                VersionId = CacheEntity.ModelInformation.VersionId,
                                Content = JsonConvert.SerializeObject(CacheEntity.Model)
                            }
                        }));
                }
                else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Update)
                {
                    resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()
                        {
                            DeviceEntity = new DeviceEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Update,
                                VersionId = CacheEntity.ModelInformation.VersionId,
                                Content = JsonConvert.SerializeObject(CacheEntity.Model)
                            }
                        }));
                }

                if (resp != null && resp.RequestFailed)
                {
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                }

                if (CacheEntity.ModelInformation.PendingAction == PendingAction.Delete)
                {
                    //clean up
                    CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                    await _apiStorageService.EraseCacheEntityAsync<CacheEntity<TDevice>>();
                }
                else
                {
                    //save cache
                    CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                    await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TDevice>>();
                }
                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.SyncDevice, VerificationOption.CanAccessInternet);
        }

        #region device methods


        private string _deviceCacheFilePath;
        protected string GetDeviceCacheFilePath()
        {
            if (_deviceCacheFilePath == null)
                return _deviceCacheFilePath;

            _deviceCacheFilePath = _apiConfigurationService.GetFileName(GetModelIdentifier() + "_col.json", typeof(TDevice));

            return _deviceCacheFilePath;
        }

        private string _deviceIdentifier;
        protected string GetDeviceIdentifier()
        {
            if (_deviceIdentifier == null)
                return _deviceIdentifier;

            var model = (TDevice)Activator.CreateInstance(typeof(TDevice));
            _deviceIdentifier = model.GetClassIdentifier();

            return _deviceIdentifier;
        }


        private readonly AsyncLock _deviceLock = new AsyncLock();
        private CollectionCacheEntity<TDevice> _deviceCache;
        private async Task<bool> InitializeDevicesAsync()
        {
            using (await _deviceLock.LockAsync())
            {
                if (_deviceCache != null)
                    return true;

                _deviceManager = _apiConfigurationService.GetCollectionManager<TDevice>();
                _deviceCache = await _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<TDevice>>(GetDeviceCacheFilePath());
                foreach (var deviceCacheModel in _deviceCache.Models)
                {
                    _deviceManager.Add(deviceCacheModel);
                }

                return true;
            }
        }

        private ICollectionManager<TDevice> _deviceManager;
        public ObservableCollection<TDevice> GetAllLazy()
        {
#pragma warning disable 4014
            InitializeDevicesAsync().ContinueWith((t) => SyncDevicesAsync());
#pragma warning restore 4014

            return _deviceManager.GetObservableCollection();
        }

        public Task<ObservableCollection<TDevice>> GetAllAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                await InitializeDevicesAsync().ContinueWith(t => SyncDevicesAsync());

                return new Tuple<ObservableCollection<TDevice>, SyncActionError>(_deviceManager.GetObservableCollection(), SyncActionError.None);
            }, SyncAction.GetAllDevices, VerificationOption.CanAccessInternet | VerificationOption.IsAuthenticatedFully);
        }

        public Task<bool> SyncDevicesAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                await InitializeDevicesAsync();

                //sync devices
                var req = new CollectionEntityRequest();
                // this will return missing, updated & removed entities
                foreach (var collectionCacheModelInformation in _deviceCache.ModelInformations)
                {
                    req.CollectionEntities.Add(new SyncEntity()
                    {
                        Id = collectionCacheModelInformation.Id,
                        VersionId = collectionCacheModelInformation.VersionId,
                        OnlineAction = OnlineAction.ConfirmVersion
                    });
                }
                var resp = await _authApiClient.GetDevicesAsync(AuthorizeRequest(ApiInformation, _apiRoamingEntity, req));
                if (!resp.IsSuccessfull)
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);

                foreach (var syncEntity in resp.SyncEntities)
                {
                    //new!
                    if (syncEntity.OnlineAction == OnlineAction.Create)
                    {
                        var mi = ApiEntityHelper.CreateCacheInformation<CacheInformations>(syncEntity);
                        var tcol = JsonConvert.DeserializeObject<TDevice>(syncEntity.Content);
                        tcol.SetId(mi.Id);
                        _deviceCache.ModelInformations.Add(mi);
                        _deviceCache.Models.Add(tcol);
                        _deviceManager.Add(tcol);
                    }
                    //updated
                    else if (syncEntity.OnlineAction == OnlineAction.Update)
                    {
                        var index = _deviceCache.ModelInformations.FindIndex(d => d.Id == syncEntity.Id);
                        _deviceCache.ModelInformations[index].VersionId = syncEntity.VersionId;
                        var model = JsonConvert.DeserializeObject<TDevice>(syncEntity.Content);
                        model.SetId(syncEntity.Id);
                        _deviceManager.Replace(_deviceCache.Models[index], model);
                        _deviceCache.Models[index] = model;
                    }
                    //removed
                    else if (syncEntity.OnlineAction == OnlineAction.Delete)
                    {
                        var index = _deviceCache.ModelInformations.FindIndex(d => d.Id == syncEntity.Id);
                        _deviceManager.Remove(_deviceCache.Models[index]);
                        _deviceCache.ModelInformations.RemoveAt(index);
                        _deviceCache.Models.RemoveAt(index);
                    }
                }

                if (resp.SyncEntities.Any())
                {
                    await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TDevice>>();
                }
                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.GetAllDevices, VerificationOption.CanAccessInternet | VerificationOption.IsAuthenticatedFully);

        }
        #endregion

        #region authentication methods
        public Task<bool> UnAuthenticateAsync(TDevice device)
        {
            return ExecuteSafeAsync(async () =>
            {
                var resp = await _authApiClient.UnAuthenticateDeviceAsync(AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()
                {
                    ClientMessage = device.GetId().ToString()
                }));

                if (resp.RequestFailed)
                {
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                }

                device.SetAuthenticationState(AuthenticationState.UnAuthenticated);
                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TDevice>>();
                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.UnAuthenticateDevice, VerificationOption.IsAuthenticatedFully | VerificationOption.CanAccessInternet);
        }

        public Task<bool> AuthenticateAsync(TDevice device)
        {
            return ExecuteSafeAsync(async () =>
            {
                var resp = await _authApiClient.AuthenticateDeviceAsync(AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()
                {
                    ClientMessage = device.GetId().ToString()
                }));

                if (resp.RequestFailed)
                {
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                }

                device.SetAuthenticationState(AuthenticationState.Authenticated);
                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TDevice>>();
                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.AuthenticateDevice, VerificationOption.IsAuthenticatedFully | VerificationOption.CanAccessInternet);
        }

        public Task<string> CreateNewAuthenticationCodeAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                var resp = await _authApiClient.CreateAuthorizationCodeAsync(AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()));

                if (resp.RequestFailed)
                {
                    return new Tuple<string, SyncActionError>(default(string), SyncActionError.RequestUnsuccessful);
                }
                
                return new Tuple<string, SyncActionError>(resp.ServerMessage, SyncActionError.None);
            }, SyncAction.CreateAuthCode, VerificationOption.IsAuthenticatedFully | VerificationOption.CanAccessInternet);
        }

        public Task<bool> TryUseAuthenticationCodeAsync(string authenticationCode)
        {
            return ExecuteSafeAsync(async () =>
            {
                var resp = await _authApiClient.CreateAuthorizationCodeAsync(AuthorizeRequest(ApiInformation, _apiRoamingEntity, new AuthRequestEntity()));

                if (resp.RequestFailed)
                {
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                }
                
                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.UseAuthCode, VerificationOption.CanAccessInternet);
        }
        #endregion

        private ApiRoamingEntity _apiRoamingEntity;
        public async Task<IDeviceModel> GetDeviceAsync(ApiRoamingEntity apiRoamingEntity)
        {
            _apiRoamingEntity = apiRoamingEntity;
            await SyncAsync();
            if (Manager.GetModel().GetAuthenticationState() != AuthenticationState.Authenticated)
                await AuthenticateAsync(Manager.GetModel());
            return Manager.GetModel();
        }

        private T AuthorizeRequestBase<T>(ApiInformation apiInformation,
            ApiRoamingEntity apiRoamingInfo, T request)
            where T : BaseRequest
        {
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformation, apiRoamingInfo);
            request.UserId = _apiRoamingEntity.UserId;
            request.DeviceId = CacheEntity.Model.GetId();
            request.ApplicationId = apiInformation.ApplicationId;
            return request;
        }

        private AuthRequestEntity AuthorizeRequest(ApiInformation apiInformation,
            ApiRoamingEntity apiRoamingInfo, AuthRequestEntity request)
        {
            return AuthorizeRequestBase(apiInformation, apiRoamingInfo, request);
        }

        private CollectionEntityRequest AuthorizeRequest(ApiInformation apiInformation,
            ApiRoamingEntity apiRoamingInfo, CollectionEntityRequest request)
        {
            return AuthorizeRequestBase(apiInformation, apiRoamingInfo, request);
        }


        public override Task<TDevice> GetAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<TDevice, SyncActionError>(await GetInternalAsync(), SyncActionError.None),
                SyncAction.GetDevice,
                VerificationOption.None
            );
        }

        public override Task<bool> SaveAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<bool, SyncActionError>(await SaveInternalAsync(), SyncActionError.None),
                SyncAction.SaveDevice,
                VerificationOption.None
            );
        }

        public override Task<bool> RemoveAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<bool, SyncActionError>(await RemoveInternalAsync(), SyncActionError.None),
                SyncAction.RemoveDevice,
                VerificationOption.None
            );
        }
    }
}
