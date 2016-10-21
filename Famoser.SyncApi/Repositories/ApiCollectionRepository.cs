using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public class ApiCollectionRepository<TCollection, TDevice, TUser> : PersistentCollectionRepository<TCollection>, IApiCollectionRepository<TCollection, TDevice, TUser>
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        public ApiCollectionRepository(IApiAuthenticationService apiAuthenticationService, IApiStorageService apiStorageService, IApiConfigurationService apiConfigurationService) : base(apiAuthenticationService, apiStorageService, apiConfigurationService)
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

        public Task<bool> AddUserToCollectionAsync(TCollection collection, TUser user)
        {
            throw new NotImplementedException();
        }
    }
}
