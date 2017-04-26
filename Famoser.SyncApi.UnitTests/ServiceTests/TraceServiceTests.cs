using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.ServiceTests
{
    [TestClass]
    public class TraceServiceTests
    {
        [TestMethod]
        public async Task TestLogsAsync()
        {
            //arrange
            var testHelper = new TestHelper();
            var repo = testHelper.SyncApiHelper.ResolveRepository<NoteModel>();
            var model = new NoteModel { Content = "Hallo Welt!" };
            
            //act
            var saveRes = await repo.SaveAsync(model);

            //assert
            Assert.IsTrue(testHelper.SuccessfulRequestEventArgs.Any());
            Assert.IsTrue(testHelper.SyncActionInformations.Count > 0);
        }
    }
}
