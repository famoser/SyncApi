using System.Threading.Tasks;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.Repository
{
    [TestFixture]
    public class OfflineRepositoryTests
    {
        [Test]
        public async Task TestSave()
        {
            //arrange
            var helper = TestHelper.GetOfflineApiHelper();
            var repo = helper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
        }

        [Test]
        public async Task TestSaveAndRetrieve()
        {
            //arrange
            var ss = new StorageService();
            var helper = TestHelper.GetOfflineApiHelper(ss);
            var repo = helper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var helper2 = TestHelper.GetOfflineApiHelper(ss);
            var repo2 = helper2.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");
        }
    }
}
