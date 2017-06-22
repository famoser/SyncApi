using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.RepositoryTests
{
    [TestClass]
    public class DeviceRepositoryTests
    {
        [TestMethod]
        public async Task TestAuthDevice()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var testHelper2 = new TestHelper { StorageService = ss };
            var testHelper3 = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            var repo3 = testHelper3.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //ensure device authenticated
            Assert.IsTrue(await repo.SaveAsync(model));

            //check auth procedure
            var devRepo = testHelper.SyncApiHelper.ApiDeviceRepository;
            var authCode = await devRepo.CreateNewAuthenticationCodeAsync();
            Assert.IsTrue(!string.IsNullOrEmpty(authCode));
            var authCode2 = await devRepo.CreateNewAuthenticationCodeAsync();
            Assert.IsTrue(!string.IsNullOrEmpty(authCode2));
            var resp = await devRepo.TryUseAuthenticationCodeAsync(authCode);
            Assert.IsTrue(resp);
            var resp2 = await devRepo.TryUseAuthenticationCodeAsync(authCode);
            Assert.IsFalse(resp2);

            ss.ClearCache();
            var resp3 = await repo2.SaveAsync(model);
            Assert.IsTrue(resp3);

            //check older device
            var device = await testHelper2.SyncApiHelper.ApiDeviceRepository.GetAllAsync();
            //not authenticated!
            Assert.IsTrue(device.Count == 0);

            var res4 = await testHelper2.SyncApiHelper.ApiDeviceRepository.TryUseAuthenticationCodeAsync(authCode2);
            Assert.IsTrue(res4);

            //check older device
            var device2 = await testHelper2.SyncApiHelper.ApiDeviceRepository.GetAllAsync();
            //get all
            Assert.IsTrue(device2.Count == 2);

            //get unAuth device from new repo
            ss.ClearCache();
            var res5 = await repo3.SaveAsync(model);
            Assert.IsTrue(res5);
            var unAuthenticated = await testHelper3.SyncApiHelper.ApiDeviceRepository.GetAsync();
            Assert.IsTrue(unAuthenticated.AuthenticationState == AuthenticationState.UnAuthenticated);

            //check older device
            var device3 = await testHelper2.SyncApiHelper.ApiDeviceRepository.GetAllAsync();
            //get all
            Assert.IsTrue(device3.Count == 3);

            //authenticate device with already authenticated repo
            var authReq = await testHelper2.SyncApiHelper.ApiDeviceRepository.AuthenticateAsync(
                device3.FirstOrDefault(d => d.GetId() == unAuthenticated.Id));
            Assert.IsTrue(authReq);


            //get unAuth device from new repo
            unAuthenticated = await testHelper3.SyncApiHelper.ApiDeviceRepository.GetAsync();
            Assert.IsTrue(unAuthenticated.AuthenticationState == AuthenticationState.Authenticated);
        }
    }
}
