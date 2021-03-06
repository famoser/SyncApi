﻿using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.Integration_Tests
{
    [TestClass]
    public class OfflineRepositoryTests
    {
        [TestMethod]
        public async Task TestSaveAsync()
        {
            //arrange
            var testHelper = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            testHelper.AssertNoErrors();
        }

        [TestMethod]
        public async Task TestSaveAndRetrieveAsync()
        {
            //arrange
            var testHelper = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper
            {
                CanUserWebConnectionFunc = () => false,
                //preserve storage service to check retrieval of cache files
                StorageService = testHelper.StorageService
            };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");

            //ensure no requests
            testHelper.AssertNoErrors();
            Assert.IsFalse(testHelper.SuccessfulRequestEventArgs.Any());
            Assert.IsFalse(testHelper.FailedRequestEventArgs.Any());
        }

        [TestMethod]
        public async Task TestMultipleSaveAsync()
        {
            //arrange
            var testHelper = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();

            //act
            var saveRes = await repo.SaveAsync(model);
            var saveRes2 = await repo2.SaveAsync(model);
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(saveRes2);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");

            //act
            var saveRes3 = await repo.SaveAsync(model);
            var saveRes4 = await repo.SaveAsync(model);
            var model3 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes3);
            Assert.IsTrue(saveRes4);
            Assert.IsTrue(model3.Count == 1);
            Assert.IsTrue(model3[0].Content == "Hallo Welt!");
            testHelper.AssertNoErrors();
            testHelper2.AssertNoErrors();
        }
    }
}
