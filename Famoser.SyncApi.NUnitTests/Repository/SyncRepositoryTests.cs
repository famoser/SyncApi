using System.Threading.Tasks;
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
    }
}
