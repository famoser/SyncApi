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
    public class CollectionRepositoryTests
    {
        [TestMethod]
        public async Task TestUserAddAsync()
        {
            var testHelper = new TestHelper { StorageService = new StorageService() };
            var testHelper2 = new TestHelper { StorageService = new StorageService() };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };

            //save one model
            var res = await repo.SaveAsync(model);
            var collRepo = testHelper.SyncApiHelper.ApiCollectionRepository;
            var collections = await collRepo.GetAllAsync();
            Assert.IsTrue(res);
            Assert.IsTrue(collections.Count == 1);

            //create a new, different user and give it collection access
            var user = await testHelper2.SyncApiHelper.ApiUserRepository.GetAsync();
            Assert.IsNotNull(user);
            var res2 = await collRepo.AddUserToCollectionAsync(collections[0], user);
            Assert.IsTrue(res2);

            //check if new user can receive the model
            var res3 = await repo2.SyncAsync();
            Assert.IsTrue(res3);
            var models = await repo2.GetAllAsync();
            Assert.IsTrue(models.Count == 1);
            Assert.IsTrue(models.First().Content == "Hallo Welt!");
        }

        [TestMethod]
        public async Task TestCollectionDefaultBehaviorAsync()
        {
            //use default coll
            var testHelper = new TestHelper { StorageService = new StorageService() };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var collRepo = testHelper.SyncApiHelper.ApiCollectionRepository;
            var coll = new CollectionModel();
            var nm = new NoteModel() { Content = "Hallo Welt" };

            var res = await collRepo.SaveAsync(coll);
            var res2 = await repo.SaveAsync(nm);
            Assert.IsTrue(res);
            Assert.IsTrue(res2);

            var colls = await collRepo.GetAllAsync();
            Assert.IsTrue(colls.Count == 2);
            var cinfos = repo.GetCacheInformations(nm);
            Assert.IsTrue(cinfos?.CollectionId == colls[0].GetId());

            //BEHAVIOR 2: use default as parent
            var testHelper2 = new TestHelper { StorageService = new StorageService() };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            var collRepo2 = testHelper2.SyncApiHelper.ApiCollectionRepository;

            var res4 = await repo2.SaveAsync(nm);
            var res3 = await collRepo2.SaveAsync(coll);
            Assert.IsTrue(res3);
            Assert.IsTrue(res4);

            var colls2 = await collRepo.GetAllAsync();
            Assert.IsTrue(colls2.Count == 2);
            var cinfos2 = repo.GetCacheInformations(nm);
            Assert.IsTrue(colls.Any(c => c.GetId() == cinfos2?.CollectionId));
            Assert.IsTrue(colls2[0].GetId() != colls2[1].GetId());
        }

        /// <summary>
        /// Check additional corner cases:
        /// - Remove all collections; you must create a new default collection
        /// - check that entities are removed once the collection is removed
        /// 
        /// "solution is left as an exercise to the reader"
        /// </summary>
        /// <returns></returns>
        [TestMethod, Ignore]
        public async Task TestCollectionRemoveBehaviorAsync()
        {
            //todo: what happens if I remove all collections?

            var testHelper = new TestHelper { StorageService = new StorageService() };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var collRepo = testHelper.SyncApiHelper.ApiCollectionRepository;
            var coll = new CollectionModel();
            var nm = new NoteModel() { Content = "Hallo Welt" };

            var res = await collRepo.SaveAsync(coll);
            var res2 = await repo.SaveAsync(nm);
            var repoInhalt = await repo.GetAllAsync();
            Assert.IsTrue(res);
            Assert.IsTrue(res2);
            Assert.IsTrue(repoInhalt.Count == 1);

            var colls = await collRepo.GetAllAsync();
            Assert.IsTrue(colls.Count == 2);
            var cinfos = repo.GetCacheInformations(nm);
            Assert.IsTrue(cinfos?.CollectionId == colls[0].GetId());

            //now remove collection
            var res5 = await collRepo.RemoveAsync(colls[0]);
            Assert.IsTrue(res5);
            var sync = await repo.GetAllAsync();
            Assert.IsTrue(sync.Count == 0);

            //BEHAVIOR 2: use default created as parent if already exists
            var testHelper2 = new TestHelper { StorageService = new StorageService() };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            var collRepo2 = testHelper2.SyncApiHelper.ApiCollectionRepository;

            var res4 = await repo2.SaveAsync(nm);
            var res3 = await collRepo2.SaveAsync(coll);
            Assert.IsTrue(res3);
            Assert.IsTrue(res4);

            var colls2 = await collRepo.GetAllAsync();
            Assert.IsTrue(colls2.Count == 2);
            var cinfos2 = repo.GetCacheInformations(nm);
            Assert.IsTrue(colls.Any(c => c.GetId() == cinfos2?.CollectionId));
            Assert.IsTrue(colls2[0].GetId() != colls2[1].GetId());

            //now remove collection
            var res6 = await collRepo.RemoveAsync(colls.First(c => c.GetId() == cinfos2.CollectionId));
            Assert.IsTrue(res6);
            var sync2 = await repo.GetAllAsync();
            Assert.IsTrue(sync2.Count == 0);
        }

        /// <summary>
        /// history disabled for collections
        /// changes needed:
        ///  - CollectionRepository: Call different url for collections
        ///  - Api: implement SyncHistory in CollectionController
        /// </summary>
        /// <returns></returns>
        [TestMethod, Ignore]
        public async Task TestCollectionHistoryAsync()
        {
            var testHelper = new TestHelper { StorageService = new StorageService() };
            var collRepo = testHelper.SyncApiHelper.ApiCollectionRepository;
            var cm = new CollectionModel();

            var res = await collRepo.SaveAsync(cm);
            var res2 = await collRepo.SaveAsync(cm);
            var res3 = await collRepo.SaveAsync(cm);
            Assert.IsTrue(res && res2 && res3);

            var history = await collRepo.GetHistoryAsync(cm);
            Assert.IsTrue(history.Count == 3);
        }
    }
}
