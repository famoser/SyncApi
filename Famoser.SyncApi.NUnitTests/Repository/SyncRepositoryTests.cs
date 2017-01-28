using System;
using System.Reflection;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Api.Communication.Response.Base;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using Famoser.SyncApi.Services;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.Repository
{
    [TestFixture]
    public class SyncRepositoryTests
    {
        [Test]
        public async Task TestSaveAndRetrieveAsync()
        {
            //arrange
            var testHelper = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };
            
            var testHelper2 = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();


            //act
            var saveRes = await repo.SaveAsync(model);
            //new instance with empty cache to ensure the notemodel is downloaded
            var model2 = await repo2.GetAllAsync();

            //assert
            Assert.IsTrue(saveRes);
            Assert.IsTrue(repo.GetAllLazy().Contains(model));
            Assert.IsTrue(model2.Count == 1);
            Assert.IsTrue(model2[0].Content == "Hallo Welt!");
        }

        [Test]
        public async Task TestSaveThreeAndRetrieveAsync()
        {
            //arrange
            var testHelper = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();

            var model = new NoteModel { Content = "Hallo Welt!" };

            var testHelper2 = new TestHelper { CanUserWebConnectionFunc = () => false };
            var repo2 = testHelper2.SyncApiHelper.ResolveRepository<NoteModel>();


            //act
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 2";
            await repo.SaveAsync(model);
            model.Content = "Hallo Welt 3";
            await repo.SaveAsync(model);
            //new instance with empty cache to ensure the notemodel is downloaded
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


            //irgnore last five bc Dispose(), ToString() etc
            for (var index = 0; index < methods.Length - 5; index++)
            {
                var methodInfo = methods[index];
                var resp = methodInfo.Invoke(client, new object[] {null});
                var tsk = resp as Task;
                if (tsk != null)
                {
                    await tsk;
                    var result = tsk.GetType().GetProperty("Result").GetValue(tsk);
                    Assert.AreNotEqual(ApiError.ResourceNotFound, ((BaseResponse) result).ApiError);
                }
            }
        }
    }
}
