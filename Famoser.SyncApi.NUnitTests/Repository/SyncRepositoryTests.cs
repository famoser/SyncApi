using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.Repository
{
    [TestFixture]
    public class SyncRepositoryTests
    {
        [Test]
        public async Task TestSaveAndRetrieve()
        {
            //arrange
            var ss = new StorageService();
            var helper = TestHelper.GetOnlineApiHelper(ss);

            var repo = helper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var helper2 = TestHelper.GetOnlineApiHelper(ss);
            var repo2 = helper2.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            //clear cache to ensure the notemodel is downloaded
            ss.ClearCache();
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");
        }

        [Test]
        public async Task TestSaveThreeAndRetrieve()
        {
            //arrange
            var ss = new StorageService();
            var helper = TestHelper.GetOnlineApiHelper(ss);

            var repo = helper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var helper2 = TestHelper.GetOnlineApiHelper(ss);
            var repo2 = helper2.ResolveRepository<NoteModel>();

            //act
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 2";
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 3";
            await repo.SaveAsync(model);
            //clear cache to ensure the notemodel is downloaded
            ss.ClearCache();
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt 3");
            
            var history = await repo.GetHistoryAsync(model);
            Assert.IsTrue(history.Count == 3);
            Assert.IsTrue(history[0].Model.Content == "Hallo Welt!");
            Assert.IsTrue(history[1].Model.Content == "Hallo Welt 2");
            Assert.IsTrue(history[2].Model.Content == "Hallo Welt 3");
        }


        public async Task TestAllEndpoints()
        {
            var ss = new StorageService();
            var helper = TestHelper.GetOnlineApiHelper(ss);
            var traceService = new ApiTraceService();
            var client = new ApiClient(new Uri(TestHelper.TestUri), traceService);

            //confirm test is up to date 
            var methods = client.GetType().GetMethods(BindingFlags.Public);
            Assert.IsTrue(methods.Length == 3);

            var req = await client.AuthenticateDeviceAsync(new AuthRequestEntity());
            Assert.AreNotEqual(ApiError.ResourceNotFound, req.ApiError);
        }
    }
}
