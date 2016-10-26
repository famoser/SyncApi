using System.Threading.Tasks;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests
{
    [TestFixture]
    public class RepositoryTests
    {
        [Test]
        public async Task TestSync()
        {
            //arrange
            var helper = new SyncApiHelper(new StorageService(), "test", "https://test.syncapi.famoser.ch");
            var repo = helper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
        }
    }
}
