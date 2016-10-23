using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Models;
using Famoser.SyncApi.Repositories;
using GalaSoft.MvvmLight.Ioc;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests
{
    [TestFixture]
    public class RepositoryTests
    {
        [SetUp]
        public void InitializeIocContainer()
        {
            IocHelper.InitializeContainer();
        }

        [Test]
        public async Task TestSync()
        {
            //arrange
            var repo = SimpleIoc.Default.GetInstanceWithoutCaching<ApiRepository<NoteModel, CollectionModel>>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
        }
    }
}
