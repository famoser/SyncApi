using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.RepositoryTests
{
    [TestClass]
    public class EntityRepositoryTests
    {
        [TestMethod]
        public async Task TestAddToSpecificCollectionAsync()
        {
            //arrange
            var testHelper = new TestHelper { StorageService = new StorageService() };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var collRepo = testHelper.SyncApiHelper.ApiCollectionRepository;
            var coll1 = new CollectionModel();
            var coll2 = new CollectionModel();
            var nm1 = new NoteModel() { Content = "Hallo Welt1" };
            var nm2 = new NoteModel() { Content = "Hallo Welt2" };
            var nm3 = new NoteModel() { Content = "Hallo Welt3" };
            var nm4 = new NoteModel() { Content = "Hallo Welt4" };

            //act
            var res = await collRepo.SaveAsync(coll1);
            var res2 = await collRepo.SaveAsync(coll2);
            var res3 = await repo.SaveAsync(nm1);
            var res4 = await repo.SaveToCollectionAsync(nm2, coll1);
            var res5 = await repo.SaveToCollectionAsync(nm3,coll2);
            var res6 = await repo.SaveAsync(nm4);
            var info1 = repo.GetCacheInformations(nm1);
            var info2 = repo.GetCacheInformations(nm2);
            var info3 = repo.GetCacheInformations(nm3);
            var info4 = repo.GetCacheInformations(nm4);
            var repoInhalt = await repo.GetAllAsync();
            var collInhalt = await collRepo.GetAllAsync();

            //assert
            Assert.IsTrue(res);
            Assert.IsTrue(res2);
            Assert.IsTrue(res3);
            Assert.IsTrue(res4);
            Assert.IsTrue(res5);
            Assert.IsTrue(res6);
            Assert.IsTrue(repoInhalt.Count == 4);
            Assert.IsTrue(collInhalt.Count == 3);
            Assert.IsTrue(info1.CollectionId == info4.CollectionId);
            Assert.IsTrue(info1.CollectionId != info3.CollectionId);
            Assert.IsTrue(info2.CollectionId == coll1.GetId());
            Assert.IsTrue(info3.CollectionId == coll2.GetId());
            //test the test
            Assert.IsTrue(coll1.GetId() != coll2.GetId());
        }
    }
}
