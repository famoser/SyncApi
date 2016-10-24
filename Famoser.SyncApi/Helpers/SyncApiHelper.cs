using System;
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
    public class SyncApiHelper : IDisposable
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        private readonly ApiUserRepository<UserModel> _apiUserRepository;
        private readonly ApiDeviceRepository<DeviceModel> _apiDeviceRepository;
        private readonly ApiCollectionRepository<CollectionModel> _apiCollectionRepository;

        public SyncApiHelper(IStorageService storageService, string applicationName, string uri = "https://public.syncapi.famoser.ch")
        {
            _apiConfigurationService = new ApiConfigurationService(applicationName, uri);
            _apiStorageService = new ApiStorageService(storageService, _apiConfigurationService);
            _apiUserRepository = new ApiUserRepository<UserModel>(_apiConfigurationService, _apiStorageService);
            _apiDeviceRepository = new ApiDeviceRepository<DeviceModel>(_apiConfigurationService, _apiStorageService);
            _apiAuthenticationService = new ApiAuthenticationService(_apiConfigurationService, _apiUserRepository, _apiDeviceRepository);
            _apiCollectionRepository = new ApiCollectionRepository<CollectionModel>(_apiAuthenticationService, _apiStorageService, _apiConfigurationService);
        }

        public ApiRepository<T, CollectionModel> ResolveRepository<T>()
            where T : ISyncModel
        {
            return new ApiRepository<T, CollectionModel>(_apiConfigurationService, _apiStorageService, _apiAuthenticationService);
        }

        private bool _isDisposed;
        protected virtual void Dispose(bool disposing)
        {
            if (_isDisposed)
                if (disposing)
                {
                    _apiUserRepository.Dispose();
                    _apiDeviceRepository.Dispose();
                    _apiCollectionRepository.Dispose();
                }
            _isDisposed = true;
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }
    }
}
