using System.Threading.Tasks;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.RepositoryTests
{
    [TestClass]
    public class DeviceRepositoryTests
    {
        [TestMethod, Ignore]
        public async Task TestAuthDevice()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };
            
            //ensure device authenticated
            Assert.IsTrue(await repo.SaveAsync(model));

            //reinit auth
            ss.ClearRoaming();
            Assert.IsTrue(await repo.SaveAsync(model));
            
            //check auth procedure
            var devRepo = testHelper.SyncApiHelper.ApiDeviceRepository;
            var authCode = await devRepo.CreateNewAuthenticationCodeAsync();
            Assert.IsTrue(!string.IsNullOrEmpty(authCode));
            var resp = await devRepo.TryUseAuthenticationCodeAsync(authCode);
            Assert.IsTrue(resp);
            
            //check older device
            var device = await devRepo.GetAllAsync();
            //todo: shouldn't this be 2?
            Assert.IsTrue(device.Count == 2);
        }
    }
}
