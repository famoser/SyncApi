﻿using System;
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
            //remove user & all other stuff
            await testHelper.SyncApiHelper.ApiDeviceRepository.RemoveAsync();
            //three files: user & roaming x2
            Assert.IsTrue(ss.CountAllCachedFiles() + ss.CountAllRoamingFiles() == 3);

            //try to retrieve again
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);

            Assert.IsTrue(model2.Count == 1);
        }
    }
}
