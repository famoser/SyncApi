using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.Services;

namespace Famoser.SyncApi.NUnitTests.Helpers
{
    public class TestHelper
    {
        private static string _applicationId = "test_appl";
        private static long _applicationSeed = 1341451215173;
        private static string _testUri = "https://testing.syncapi.famoser.ch/";
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
