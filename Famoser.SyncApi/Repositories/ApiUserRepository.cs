using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiUserRepository<TUser> : PersistentRepository<TUser>, IApiUserRepository<TUser>
        where TUser : class, IUserModel
    {
        private readonly ApiClient _authApiClient;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiTraceService _apiTraceService;

        public ApiUserRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiTraceService traceService)
            : base(apiConfigurationService, apiStorageService, traceService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
            _apiTraceService = traceService;

            _authApiClient = GetAuthApiClient();
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ApiRoamingEntity _roaming;
        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CacheEntity != null)
                    return true;

                _roaming = await _apiStorageService.GetApiRoamingEntityAsync();
                if (_roaming.UserId == Guid.Empty)
                {
                    //totally new installation
                    _roaming.UserId = Guid.NewGuid();
                    _roaming.AuthenticationState = AuthenticationState.NotYetAuthenticated;
                    _roaming.CreatedAt = DateTime.Now;

                    var info = _apiTraceService.CreateSyncActionInformation(SyncAction.CreateUser);
                    info.SetSyncActionResult(SyncActionError.None);

                    var random = new Random(ApiInformation.ApplicationSeed);
                    _roaming.PersonalSeed = random.Next();
                    await _apiStorageService.SaveApiRoamingEntityAsync(_roaming);

                    CacheEntity = await _apiStorageService.GetCacheEntityAsync<CacheEntity<TUser>>(GetModelCacheFilePath());
                    CacheEntity.Model = await _apiConfigurationService.GetUserObjectAsync<TUser>();
                    CacheEntity.ModelInformation = new CacheInformations()
                    {
                        Id = _roaming.UserId,
                        PendingAction = PendingAction.Create,
                        VersionId = Guid.NewGuid(),
                        CreateDateTime = DateTime.Now
                    };
                    await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TUser>>();
                }
                else
                {
                    CacheEntity = await _apiStorageService.GetCacheEntityAsync<CacheEntity<TUser>>(GetModelCacheFilePath());
                    if (CacheEntity.ModelInformation == null)
                    {
                        CacheEntity.ModelInformation = new CacheInformations()
                        {
                            Id = _roaming.UserId,
                            PendingAction = PendingAction.Read
                        };
                        await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TUser>>();
                    }

                    var info = _apiTraceService.CreateSyncActionInformation(SyncAction.FoundUser);
                    info.SetSyncActionResult(SyncActionError.None);
                }
                Manager.Set(CacheEntity.Model);

                return true;
            }
        }

        public override Task<bool> SyncAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                if (CacheEntity.ModelInformation.PendingAction == PendingAction.None)
                    return new Tuple<bool, SyncActionError>(true, SyncActionError.None); ;

                if (CacheEntity.ModelInformation.PendingAction == PendingAction.Create)
                {
                    var json = JsonConvert.SerializeObject(CacheEntity.Model);
                    var resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                        {
                            UserEntity = new UserEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Create,
                                VersionId = CacheEntity.ModelInformation.VersionId,
                                Content = json,
                                PersonalSeed = _roaming.PersonalSeed,
                                CreateDateTime = CacheEntity.ModelInformation.CreateDateTime,
                                Identifier = CacheEntity.Model.GetClassIdentifier()
                            }
                        }));
                    if (resp.RequestFailed)
                    {
                        return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                    }
                    _roaming.AuthenticationState = AuthenticationState.Authenticated;
                }
                else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Read)
                {
                    var resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                        {
                            UserEntity = new UserEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Read
                            }
                        }));
                    if (resp.IsSuccessfull)
                    {
                        var user = JsonConvert.DeserializeObject<TUser>(resp.UserEntity.Content);
                        user.SetId(CacheEntity.ModelInformation.Id);
                        Manager.Set(user);
                    }
                    else
                        return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                }
                else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Update)
                {
                    var json = JsonConvert.SerializeObject(CacheEntity.Model);
                    var resp = await _authApiClient.DoSyncRequestAsync(
                        AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                        {
                            UserEntity = new UserEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Update,
                                VersionId = CacheEntity.ModelInformation.VersionId,
                                Content = json,
                                CreateDateTime = CacheEntity.ModelInformation.CreateDateTime
                            }
                        }));
                    if (resp.RequestFailed)
                    {
                        return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                    }
                }
                else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Delete || CacheEntity.ModelInformation.PendingAction == PendingAction.DeleteLocally)
                {
                    if (CacheEntity.ModelInformation.PendingAction == PendingAction.Delete)
                    {
                        var req = new AuthRequestEntity()
                        {
                            UserEntity = new UserEntity()
                            {
                                Id = CacheEntity.ModelInformation.Id,
                                OnlineAction = OnlineAction.Delete
                            },
                        };

                        //check if authorized
                        var service = GetApiAuthenticationService();
                        var deviceId = service.TryGetDeviceId();
                        if (deviceId.HasValue)
                        {
                            req.DeviceId = deviceId.Value;
                        }
                        var resp = await _authApiClient.DoSyncRequestAsync(AuthorizeRequest(ApiInformation, _roaming, req));
                        if (resp.RequestFailed)
                        {
                            return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);
                        }
                    }

                    //clean up
                    _roaming.UserId = Guid.Empty;
                    _roaming.AuthenticationState = AuthenticationState.UnAuthenticated;
                    CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                    await _apiStorageService.EraseRoamingAndCacheAsync();
                    await GetApiAuthenticationService().CleanUpAfterUserRemoveAsync();
                    await CleanUpAsync();

                    return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
                }

                CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TUser>>();
                await _apiStorageService.SaveApiRoamingEntityAsync(_roaming);

                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.SyncUser, VerificationOption.CanAccessInternet);
        }


        private AuthRequestEntity AuthorizeRequest(ApiInformation apiInformation,
            ApiRoamingEntity apiRoamingInfo, AuthRequestEntity request)
        {
            request.ApplicationId = apiInformation.ApplicationId;
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformation, apiRoamingInfo);
            request.UserId = _roaming.UserId;
            request.Identifier = GetModelIdentifier();
            return request;
        }

        public async Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            //double validation for small perf. boost
            if (CacheEntity?.ModelInformation == null || CacheEntity.ModelInformation.PendingAction != PendingAction.None)
                await SyncAsync();

            return _roaming;
        }

        public override Task<TUser> GetAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<TUser, SyncActionError>(await GetInternalAsync(), SyncActionError.None),
                SyncAction.GetUser,
                VerificationOption.None
            );
        }

        public override Task<bool> SaveAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<bool, SyncActionError>(await SaveInternalAsync(), SyncActionError.None),
                SyncAction.SaveUser,
                VerificationOption.None
            );
        }

        public override Task<bool> RemoveAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<bool, SyncActionError>(await RemoveInternalAsync(), SyncActionError.None),
                SyncAction.RemoveUser,
                VerificationOption.None
            );
        }

        public override Task<bool> CleanUpAsync()
        {
            CacheEntity = null;
            return base.CleanUpAsync();
        }
    }
}
