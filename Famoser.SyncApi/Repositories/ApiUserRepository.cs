using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Configuration;
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

        public ApiUserRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiTraceService traceService)
            : base(apiConfigurationService, apiStorageService, traceService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;

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

                    var random = new Random(ApiInformation.ApplicationSeed);
                    _roaming.PersonalSeed = random.Next();
                    await _apiStorageService.SaveApiRoamingEntityAsync();

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
                }
                Manager.Set(CacheEntity.Model);

                return true;
            }
        }

        protected override async Task<bool> SyncInternalAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction == PendingAction.None)
                return true;

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
                    return false;
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
                    return false;
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
                    return false;
                }
            }
            else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Delete)
            {
                var resp = await _authApiClient.DoSyncRequestAsync(
                    AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = CacheEntity.ModelInformation.Id,
                            OnlineAction = OnlineAction.Delete
                        }
                    }));
                if (resp.RequestFailed)
                {
                    return false;
                }

                //clean up
                _roaming.UserId = Guid.Empty;
                _roaming.AuthenticationState = AuthenticationState.UnAuthenticated;
                CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                return await _apiStorageService.EraseRoamingAndCacheAsync();
            }
            else
                return true;

            CacheEntity.ModelInformation.PendingAction = PendingAction.None;
            return await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TUser>>();
        }


        private AuthRequestEntity AuthorizeRequest(ApiInformation apiInformation,
            ApiRoamingEntity apiRoamingInfo, AuthRequestEntity request)
        {
            request.ApplicationId = apiInformation.ApplicationId;
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformation, apiRoamingInfo);
            request.UserId = _roaming.UserId;
            return request;
        }

        public async Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            await ExecuteSafeAsync(async () =>
            {
                if (_apiConfigurationService.CanUseWebConnection())
                    await SyncInternalAsync();
            });
            return _roaming;
        }
    }
}
