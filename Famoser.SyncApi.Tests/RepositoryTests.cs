using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Repositories;
using Famoser.SyncApi.Services;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Tests.Implementations;
using Famoser.SyncApi.Tests.Models;
using GalaSoft.MvvmLight.Ioc;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.Tests
{
    [TestClass]
    public class RepositoryTests
    {
        [TestInitialize]
        public void InitializeIocContainer()
        {
            SimpleIoc.Default.Register<IApiConfigurationService, ApiConfigurationService>();
            SimpleIoc.Default.Register<IApiStorageService, ApiStorageService>();
            var userRep = SimpleIoc.Default.GetInstanceWithoutCaching<ApiUserRepository<UserModel>>();
            SimpleIoc.Default.Register<IApiAuthenticationService, ApiAuthenticationService>();
        }
        [TestMethod]
        public async Task TestSync()
        {
            //arrange
            var repo = SimpleIoc.Default.GetInstanceWithoutCaching<ApiRepository<NoteModel, CollectionModel, DeviceModel, UserModel>>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
        }
    }
}
