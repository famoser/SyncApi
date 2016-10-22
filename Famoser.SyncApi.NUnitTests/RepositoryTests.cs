using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using Famoser.SyncApi.Repositories;
using Famoser.SyncApi.Services;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
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
            SimpleIoc.Default.Register<IApiConfigurationService, ApiConfigurationService>();
            SimpleIoc.Default.Register<IApiStorageService, ApiStorageService>();
            var userRep = SimpleIoc.Default.GetInstanceWithoutCaching<ApiUserRepository<UserModel>>();
            var deviceRepo = SimpleIoc.Default.GetInstanceWithoutCaching<ApiDeviceRepository<DeviceModel>>();
            var aus = new ApiAuthenticationService(userRep, deviceRepo, SimpleIoc.Default.GetInstance<IApiConfigurationService>());
            SimpleIoc.Default.Register<IApiAuthenticationService>(() => aus);
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
