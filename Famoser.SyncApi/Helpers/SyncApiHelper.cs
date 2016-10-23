using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Repositories;
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

        public SyncApiHelper(string applicationName, IStorageService storageService)
        {
            _apiConfigurationService = new ApiConfigurationService(applicationName);
            _apiStorageService = new ApiStorageService(storageService, _apiConfigurationService);
            var userRepo = new ApiUserRepository<UserModel>(_apiConfigurationService, _apiStorageService);
            var deviceRepo = new ApiDeviceRepository<DeviceModel>(_apiConfigurationService, _apiStorageService);
            _apiAuthenticationService = new ApiAuthenticationService(_apiConfigurationService, userRepo, deviceRepo);
        }

        
        public void InitializeRepository()
        {

        }
    }
}
