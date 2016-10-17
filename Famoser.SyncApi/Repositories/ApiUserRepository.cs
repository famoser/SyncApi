using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Clients;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Cache.Entitites;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiUserRepository<TUser> : PersistentRepository<TUser>, IApiUserRepository<TUser>, IApiUserAuthenticationService
        where TUser : IUserModel
    {
        private readonly AuthApiClient _authApiClient;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiUserRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService) : base(apiConfigurationService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;

            _authApiClient = GetAuthApiClient();
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ApiRoamingEntity _roaming;

        protected override async Task<bool> SyncInternalAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction == PendingAction.None)
                return true;

            if (CacheEntity.ModelInformation.PendingAction == PendingAction.Create)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(ApiInformationEntity, _roaming, new AuthRequestEntity()
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
            }
            else if (CacheEntity.ModelInformation.PendingAction == PendingAction.Read)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(ApiInformationEntity, _roaming, new AuthRequestEntity()
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
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(ApiInformationEntity, _roaming, new AuthRequestEntity()
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
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(ApiInformationEntity, _roaming, new AuthRequestEntity()
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
                CacheEntity.ModelInformation.PendingAction = PendingAction.None;
                return await _apiStorageService.EraseAllAsync();
            }
            else
                return true;


            CacheEntity.ModelInformation.PendingAction = PendingAction.None;
            return await _apiStorageService.SaveCacheEntityAsync<TUser>();
        }

        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CacheEntity != null)
                    return true;

                _roaming = await _apiStorageService.GetApiRoamingEntity();
                if (_roaming.UserId == Guid.Empty)
                {
                    //totally new installation
                    _roaming.UserId = Guid.NewGuid();
                    var random = new Random(ApiInformationEntity.Seed);
                    _roaming.PersonalSeed = random.Next();
                    await _apiStorageService.SaveApiRoamingEntityAsync();

                    CacheEntity = await _apiStorageService.GetCacheEntity<TUser>();
                    CacheEntity.Model = await _apiConfigurationService.GetUserObjectAsync<TUser>();
                    CacheEntity.ModelInformation = new ModelInformation()
                    {
                        Id = _roaming.UserId,
                        PendingAction = PendingAction.Create,
                        VersionId = Guid.NewGuid()
                    };
                    await _apiStorageService.SaveCacheEntityAsync<TUser>();
                }
                else
                {
                    CacheEntity = await _apiStorageService.GetCacheEntity<TUser>();
                    if (CacheEntity.ModelInformation == null)
                    {
                        CacheEntity.ModelInformation = new ModelInformation()
                        {
                            Id = _roaming.UserId,
                            PendingAction = PendingAction.Read
                        };
                        await _apiStorageService.SaveCacheEntityAsync<TUser>();
                    }
                }

                return true;
            }
        }

        private AuthRequestEntity AuthorizeRequest(ApiInformationEntity apiInformationEntity,
            ApiRoamingEntity apiRoamingInfo, AuthRequestEntity request)
        {
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformationEntity, apiRoamingInfo);
            request.UserId = _roaming.UserId;
            return request;
        }

        public async Task<ApiRoamingEntity> TryGetApiRoamingEntityAsync()
        {
            if (!await InitializeAsync())
                return null;

            return _roaming;
        }
    }
}
