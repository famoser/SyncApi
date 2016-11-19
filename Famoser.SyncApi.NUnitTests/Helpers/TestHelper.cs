using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.Services;

namespace Famoser.SyncApi.NUnitTests.Helpers
{
    public class TestHelper
    {
        private static string _applicationId = "test_appl";
        private static string _testUri = "http://localhost";
        public static SyncApiHelper GetOfflineApiHelper(IStorageService storageService = null)
        {
            if (storageService == null)
                storageService = new StorageService();
            return new SyncApiHelper(storageService, _applicationId, _testUri)
            {
                ApiConfigurationService = new ApiConfigurationService(_applicationId, _testUri, () => false)
            };
        }
        public static SyncApiHelper GetOnlineApiHelper(IStorageService storageService = null)
        {
            if (storageService == null)
                storageService = new StorageService();
            return new SyncApiHelper(storageService, _applicationId, _testUri)
            {
                ApiConfigurationService = new ApiConfigurationService(_applicationId, _testUri, () => true)
            };
        }
    }
}
