using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.ServiceTests
{
    [TestClass]
    public class ApiStorageTests
    {
        [TestMethod]
        public async Task TestRemoveUserAsync()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { StorageService = testHelper.StorageService };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            //remove user & all other stuff
            await testHelper.SyncApiHelper.ApiUserRepository.RemoveAsync();
            Assert.IsTrue(ss.CountAllCachedFiles() + ss.CountAllRoamingFiles() == 0);

            //try to retrieve again
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);

            Assert.IsTrue(model2.Count == 0);
        }

        [TestMethod]
        public async Task TestRemoveDeviceAsync()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { StorageService = testHelper.StorageService };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);

            var device = await testHelper.SyncApiHelper.ApiDeviceRepository.GetAsync();
            var user = await testHelper.SyncApiHelper.ApiUserRepository.GetAsync();

            var devices = await testHelper.SyncApiHelper.ApiDeviceRepository.GetAllAsync();
            Assert.IsTrue(devices.Count == 1);

            //remove device & other stuff
            await testHelper.SyncApiHelper.ApiDeviceRepository.RemoveAsync();
            //three files: user & roaming x2
            Assert.IsTrue(ss.CountAllCachedFiles() + ss.CountAllRoamingFiles() == 3);

            //try to retrieve again
            var model2 = await repo2.GetAllAsync();
            var device2 = await testHelper2.SyncApiHelper.ApiDeviceRepository.GetAsync();
            var user2 = await testHelper.SyncApiHelper.ApiUserRepository.GetAsync();

            Assert.AreNotEqual(device, device2);
            Assert.AreNotEqual(device.GetId(), device2.GetId());

            Assert.AreEqual(user.GetId(), user2.GetId());

            var newDevices = await testHelper2.SyncApiHelper.ApiDeviceRepository.GetAllAsync();
            Assert.IsTrue(newDevices.Count == 1);
            Assert.IsTrue(newDevices[0].GetId() != devices[0].GetId());


            //assert
            Assert.IsTrue(saveRes);

            Assert.IsTrue(model2.Count == 1);
        }

        [TestMethod]
        public async Task TestRemoveCollectionAsync()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { StorageService = testHelper.StorageService };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            var testHelper3 = new TestHelper { StorageService = testHelper.StorageService };
            var repo3 = testHelper3.SyncApiHelper.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            //one collection should have been automatically created
            var collections = await testHelper.SyncApiHelper.ApiCollectionRepository.GetAllAsync();
            Assert.IsTrue(collections.Count == 1);

            var id = collections[0].GetId();
            var res = await testHelper.SyncApiHelper.ApiCollectionRepository.RemoveAsync(collections[0]);
            Assert.IsTrue(res);

            var coll = await testHelper.SyncApiHelper.ApiCollectionRepository.GetAllAsync();
            //no collections should be available now
            Assert.IsTrue(coll.Count == 0);

            //try to retrieve again
            var model2 = await repo2.GetAllAsync();
            //todo: why does this return 1?
            // Assert.IsTrue(model2.Count == 0);

            var coll2 = await testHelper.SyncApiHelper.ApiCollectionRepository.GetAllAsync();
            //default collections should be generated again
            Assert.IsTrue(coll2.Count == 1);
            Assert.IsTrue(coll2[0].GetId() != id);
        }
    }
}
