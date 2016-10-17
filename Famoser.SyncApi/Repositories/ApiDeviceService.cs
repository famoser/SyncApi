using System;
using System.Collections.ObjectModel;
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
    public class ApiDeviceService<TDevice, TUser> : PersistentRepository<TDevice>, IApiDeviceRepository<TDevice, TUser>, IApiDeviceAuthenticationService
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        public ApiDeviceService(IApiConfigurationService apiConfigurationService) : base(apiConfigurationService)
        {
        }

        protected override Task<bool> SyncInternalAsync()
        {
            throw new NotImplementedException();
        }

        protected override Task<bool> InitializeAsync()
        {
            throw new NotImplementedException();
        }

        public ObservableCollection<TDevice> GetAllLazy()
        {
            throw new NotImplementedException();
        }

        public Task<ObservableCollection<TDevice>> GetAllAsync()
        {
            throw new NotImplementedException();
        }

        public Task<bool> UnAuthenticateAsync(TDevice device)
        {
            throw new NotImplementedException();
        }

        public Task<bool> AuthenticateAsync(TDevice device)
        {
            throw new NotImplementedException();
        }

        public Task<string> CreateNewAuthenticationCodeAsync()
        {
            throw new NotImplementedException();
        }

        public Task<bool> TryUseAuthenticationCodeAsync(string authenticationCode)
        {
            throw new NotImplementedException();
        }

        public async Task<Guid?> GetAuthenticatedDeviceId()
        {
            if (!await InitializeAsync())
                return null;

            if (Manager.GetModel().GetAuthenticationState() == AuthenticationState.Authenticated)
                return Manager.GetModel().GetId();
            return null,
        }
    }
}
