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

        public ApiUserRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService) : base(apiConfigurationService, apiStorageService)
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

                    var random = new Random(ApiInformation.ApplicationSeed);
                    _roaming.PersonalSeed = random.Next();
                    await _apiStorageService.SaveApiRoamingEntityAsync();

                    CacheEntity = await _apiStorageService.GetCacheEntityAsync<CacheEntity<TUser>>(GetModelCacheFilePath());
                    CacheEntity.Model = await _apiConfigurationService.GetUserObjectAsync<TUser>();
                    CacheEntity.ModelInformation = new CacheInformations()
                    {
                        Id = _roaming.UserId,
                        PendingAction = PendingAction.Create,
                        VersionId = Guid.NewGuid()
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
                var resp = await _authApiClient.DoSyncRequestAsync(
                    AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = CacheEntity.ModelInformation.Id,
                            OnlineAction = OnlineAction.Create,
                            VersionId = CacheEntity.ModelInformation.VersionId,
                            Content = JsonConvert.SerializeObject(CacheEntity.Model),
                            PersonalSeed = _roaming.PersonalSeed
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
                    Manager.Set(JsonConvert.DeserializeObject<TUser>(resp.UserEntity.Content));
                }
                else
                    return false;
            }
            else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Update)
            {
                var resp = await _authApiClient.DoSyncRequestAsync(
                    AuthorizeRequest(ApiInformation, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = CacheEntity.ModelInformation.Id,
                            OnlineAction = OnlineAction.Update,
                            VersionId = CacheEntity.ModelInformation.VersionId,
                            Content = JsonConvert.SerializeObject(CacheEntity.Model)
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
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformation, apiRoamingInfo);
            request.UserId = _roaming.UserId;
            return request;
        }

        public async Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            await ExecuteSafe(async () =>
            {
                if (_apiConfigurationService.CanUseWebConnection())
                    await SyncInternalAsync();
            });
            return _roaming;
        }
    }
}
