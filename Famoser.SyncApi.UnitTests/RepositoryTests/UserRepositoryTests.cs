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
    public class UserRepositoryTests
    {
        [TestMethod]
        public async Task TestReplaceUser()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();

            //arrange
            var ss2 = new StorageService();
            var testHelper2 = new TestHelper { StorageService = ss2 };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            var model = new NoteModel { Content = "Hallo Welt!" };

            //ensure device authenticated
            Assert.IsTrue(await repo.SaveAsync(model));

            //ensure nothings in repo2 (as it is a different user)
            Assert.IsTrue((await repo2.GetAllAsync()).Count == 0);

            //replace user
            var user = await testHelper.SyncApiHelper.ApiUserRepository.GetAsync();
            Assert.IsTrue(await testHelper2.SyncApiHelper.ApiUserRepository.ReplaceUserAsync(user));

            //auth device
            var authCode = await testHelper.SyncApiHelper.ApiDeviceRepository.CreateNewAuthenticationCodeAsync();
            Assert.IsTrue(!string.IsNullOrEmpty(authCode));
            Assert.IsTrue(await testHelper2.SyncApiHelper.ApiDeviceRepository.TryUseAuthenticationCodeAsync(authCode));
            
            //ensure model is now in repo2
            var models = await repo2.GetAllAsync();
            Assert.IsTrue(models.Count == 1);

            //ensure usermolde is not null
            var userModel = await testHelper2.SyncApiHelper.ApiUserRepository.GetAsync();
            Assert.IsNotNull(userModel);
            Assert.Equals(userModel.Id, user.GetId());

            //ensure it is a persistent change
            testHelper2 = new TestHelper { StorageService = ss2 };
            repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            models = await repo2.GetAllAsync();
            Assert.IsTrue(models.Count == 1);
        }
    }
}
