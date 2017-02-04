using System;
using System.Linq;
using System.Reflection;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Response.Base;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using Famoser.SyncApi.Services;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.Integration_Tests
{
    [TestFixture]
    public class SyncRepositoryTests
    {
        [Test]
        public async Task TestSaveAndRetrieveAsync()
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
            //new instance with empty cache to ensure the notemodel is downloaded
            ss.ClearCache();
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));

            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");
        }

        [Test]
        public async Task TestSaveAndRetrieveMessagesAsync()
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
            //new instance with empty cache to ensure the notemodel is downloaded
            ss.ClearCache();
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));

            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");

            var grouped = testHelper2.SyncActionInformations.GroupBy(s => s.SyncAction);

            foreach (var sae in grouped)
            {
                Assert.IsTrue(sae.Count() == 1);
                Assert.IsTrue(sae.First().IsCompleted);
            }
        }

        [Test]
        public async Task TestSaveAndRetrieveMessagesOnNewDeviceAsync()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };
           

            //act
            var saveRes = await repo.SaveAsync(model);
            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));

            testHelper.SyncActionInformations.Clear();
            var testHelper2 = new TestHelper { StorageService = ss };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();
            var model2 = repo2.GetAllLazy();
            await Task.Delay(4000);

            //assert 2
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");

            var grouped = testHelper.SyncActionInformations.GroupBy(s => s.SyncAction);

            foreach (var sae in grouped)
            {
                Assert.IsTrue(sae.Count() == 1);
                Assert.IsTrue(sae.First().IsCompleted);
            }
        }

        [Test]
        public async Task TestReauthenticationAsync()
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
            //new instance with empty cache to ensure the notemodel is downloaded
            var model2 = await repo2.GetAllAsync();
            //new instance with empty cache to ensure the notemodel is downloaded
            var model3 = await repo3.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));

            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");

            Assert.IsTrue(model3.Count == 1);
            Assert.IsTrue(model3[0].Content == "Hallo Welt!");
            
            Assert.IsTrue(testHelper.FailedRequestEventArgs.Count == 1); //failed auth request
            Assert.IsTrue(testHelper2.FailedRequestEventArgs.Count == 0);
            Assert.IsTrue(testHelper3.FailedRequestEventArgs.Count == 0);
        }

        [Test]
        public async Task TestSaveThreeAndRetrieveAsync()
        {
            //arrange
            var ss = new StorageService();
            var testHelper = new TestHelper { StorageService = ss };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();

            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { StorageService = testHelper.StorageService };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();


            //act
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 2";
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 3";
            await repo.SaveAsync(model);
            //new instance with empty cache to ensure the notemodel is downloaded
            ss.ClearCache();
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt 3");

            var history = await repo.GetHistoryAsync(model);
            Assert.IsTrue(history.Count == 3);
            Assert.IsTrue(history[0].Model.Content == "Hallo Welt!");
            Assert.IsTrue(history[1].Model.Content == "Hallo Welt 2");
            Assert.IsTrue(history[2].Model.Content == "Hallo Welt 3");
        }

        [Test]
        public async Task TestAllEndpointsAsync()
        {
            var traceService = new ApiTraceService();
            var client = new ApiClient(new Uri(TestHelper.TestUri), traceService);

            //confirm test is up to date 
            var methods = client.GetType().GetMethods(BindingFlags.Public | BindingFlags.Instance);
            Assert.IsTrue(methods.Length == 15);
            
            //ignore last five bc Dispose(), ToString() etc
            for (var index = 0; index < methods.Length - 5; index++)
            {
                var methodInfo = methods[index];
                var resp = methodInfo.Invoke(client, new object[] { null });
                var tsk = resp as Task;
                if (tsk != null)
                {
                    await tsk;
                    var result = tsk.GetType().GetProperty("Result").GetValue(tsk);
                    Assert.AreNotEqual(ApiError.ResourceNotFound, ((BaseResponse)result).ApiError);
                }
            }
        }
    }
}
