using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Helpers
{
    public class SyncApiHelper
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;
        private readonly IApiCollectionRepository<CollectionModel> _apiCollectionRepository;

        public SyncApiHelper(IStorageService storageService, string applicationName, string uri = "https://public.syncapi.famoser.ch")
        {
            _apiConfigurationService = new ApiConfigurationService(applicationName, uri);
            _apiStorageService = new ApiStorageService(storageService, _apiConfigurationService);
            var userRepo = new ApiUserRepository<UserModel>(_apiConfigurationService, _apiStorageService);
            var deviceRepo = new ApiDeviceRepository<DeviceModel>(_apiConfigurationService, _apiStorageService);
            _apiAuthenticationService = new ApiAuthenticationService(_apiConfigurationService, userRepo, deviceRepo);
            _apiCollectionRepository = new ApiCollectionRepository<CollectionModel>(_apiAuthenticationService, _apiStorageService, _apiConfigurationService);
        }

        public IApiRepository<T, CollectionModel> ResolveRepository<T>()
            where T : ISyncModel
        {
            return new ApiRepository<T, CollectionModel>(_apiConfigurationService, _apiStorageService, _apiAuthenticationService);
        }
    }
}
