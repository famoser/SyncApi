using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.NUnitTests.Helpers;
using Famoser.SyncApi.NUnitTests.Implementations;
using Famoser.SyncApi.NUnitTests.Models;
using NUnit.Framework;
using NUnit.Framework.Internal;

namespace Famoser.SyncApi.NUnitTests.ServiceTests
{
    [TestFixture]
    public class TraceServiceTests
    {
        [Test]
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
