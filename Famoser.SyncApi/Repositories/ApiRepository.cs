using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public class ApiRepository<TModel, TCollection, TDevice, TUser> : PersistentCollectionRepository<TModel>, IApiRepository<TModel, TCollection, TDevice, TUser>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        private readonly ICollectionManager<TModel> _collectionManager;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        public ApiRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService)
            : base(apiAuthenticationService, apiStorageService, apiConfigurationService)
        {
            _collectionManager = new CollectionManager<TModel>();
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
            _apiAuthenticationService = apiAuthenticationService;
        }


        protected override Task<bool> SyncInternalAsync()
        {
            throw new NotImplementedException();
        }

        protected override Task<bool> InitializeAsync()
        {
            throw new NotImplementedException();
        }
    }
}
