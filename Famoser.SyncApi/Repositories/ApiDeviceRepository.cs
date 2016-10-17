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
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Cache.Entitites;
using Famoser.SyncApi.Storage.Roaming;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiDeviceRepository<TDevice, TUser> : PersistentRepository<TDevice>, IApiDeviceRepository<TDevice, TUser>
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        private readonly AuthApiClient _authApiClient;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiDeviceRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
        {
            _apiInformationEntity = apiConfigurationService.GetApiInformations();
            _apiConfigurationService = apiConfigurationService;

            _apiStorageService = apiStorageService;
            _authApiClient = new AuthApiClient(_apiInformationEntity.Uri);
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private CacheEntity<TUser> _user;
        private ApiRoamingEntity _roaming;
        private readonly ApiInformationEntity _apiInformationEntity;
        private Task<bool> Initialize()
        {
            return ExecuteSafe(async () =>
            {
                using (await _asyncLock.LockAsync())
                {
                    if (_user != null)
                        return true;

                    _roaming = await _apiStorageService.GetApiRoamingEntity();
                    if (_roaming.UserId == Guid.Empty)
                    {
                        //totally new installation
                        _roaming.UserId = Guid.NewGuid();
                        var random = new Random(_apiInformationEntity.Seed);
                        _roaming.PersonalSeed = random.Next();
                        await _apiStorageService.SaveApiRoamingEntityAsync();

                        _user = await _apiStorageService.GetCacheEntity<TUser>();
                        _user.Model = await _apiConfigurationService.GetUserObjectAsync<TUser>();
                        _user.ModelInformation = new ModelInformation()
                        {
                            Id = _roaming.UserId,
                            PendingAction = PendingAction.Create,
                            VersionId = Guid.NewGuid()
                        };
                        await _apiStorageService.SaveCacheEntityAsync<TUser>();
                    }
                    else
                    {
                        _user = await _apiStorageService.GetCacheEntity<TUser>();
                        if (_user.ModelInformation == null)
                        {
                            _user.ModelInformation = new ModelInformation()
                            {
                                Id = _roaming.UserId,
                                PendingAction = PendingAction.Read
                            };
                            await _apiStorageService.SaveCacheEntityAsync<TUser>();
                        }
                    }

                    return true;
                }
            });
        }

        private AuthRequestEntity AuthorizeRequest(ApiInformationEntity apiInformationEntity,
            ApiRoamingEntity apiRoamingInfo, AuthRequestEntity request)
        {
            request.AuthorizationCode = AuthorizationHelper.GenerateAuthorizationCode(apiInformationEntity, apiRoamingInfo);
            request.UserId = _roaming.UserId;
            return request;
        }

        public async Task<bool> SyncAsync()
        {
            if (!await Initialize())
                return false;

            if (_user.ModelInformation.PendingAction == PendingAction.None)
                return true;

            if (_user.ModelInformation.PendingAction == PendingAction.Create)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(_apiInformationEntity, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = _user.ModelInformation.Id,
                            OnlineAction = OnlineAction.Create,
                            VersionId = _user.ModelInformation.VersionId,
                            Content = JsonConvert.SerializeObject(_user.Model),
                            PersonalSeed = _roaming.PersonalSeed
                        }
                    }));
                if (resp.RequestFailed)
                {
                    return false;
                }
            }
            else if (_user.ModelInformation.PendingAction == PendingAction.Read)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(_apiInformationEntity, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = _user.ModelInformation.Id,
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
            else if (_user.ModelInformation.PendingAction == PendingAction.Update)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(_apiInformationEntity, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = _user.ModelInformation.Id,
                            OnlineAction = OnlineAction.Update,
                            VersionId = _user.ModelInformation.VersionId,
                            Content = JsonConvert.SerializeObject(_user.Model)
                        }
                    }));
                if (resp.RequestFailed)
                {
                    return false;
                }
            }
            else if (_user.ModelInformation.PendingAction == PendingAction.Delete)
            {
                var resp = await _authApiClient.DoRequestAsync(
                    AuthorizeRequest(_apiInformationEntity, _roaming, new AuthRequestEntity()
                    {
                        UserEntity = new UserEntity()
                        {
                            Id = _user.ModelInformation.Id,
                            OnlineAction = OnlineAction.Delete
                        }
                    }));
                if (resp.RequestFailed)
                {
                    return false;
                }

                //clean up
                _roaming.UserId = Guid.Empty;
                _user.ModelInformation.PendingAction = PendingAction.None;
                return await _apiStorageService.EraseAllAsync();
            }
            else
                return true;


            _user.ModelInformation.PendingAction = PendingAction.None;
            return await _apiStorageService.SaveCacheEntityAsync<TUser>();
        }

        public async Task<TUser> GetAsync()
        {
            if (!await Initialize())
                return default(TUser);

            await SyncAsync();

            return Manager.GetModel();
        }


        public Task<bool> SaveAsync()
        {

            if (_user.ModelInformation.PendingAction == PendingAction.None
                || _user.ModelInformation.PendingAction == PendingAction.Delete
                || _user.ModelInformation.PendingAction == PendingAction.Read)
            {
                _user.ModelInformation.VersionId = Guid.NewGuid();
                _user.ModelInformation.PendingAction = PendingAction.Update;
                return SyncAsync();
            }
            return SyncAsync();
        }

        public Task<bool> RemoveAsync()
        {
            if (_user.ModelInformation.PendingAction != PendingAction.Create)
            {
                _user.ModelInformation.PendingAction = PendingAction.Create;
            }
            return SyncAsync();
        }
    }
}
