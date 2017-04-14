using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.HelperTests
{
    [TestClass]
    public class SyncApiHelperTests
    {
        [TestMethod]
        public async Task TestDisposingProperty()
        {
            var sah = new TestHelper();
            using (var helper = sah.SyncApiHelper)
            {
                using (var repo = helper.ResolveRepository<NoteModel>())
                {
                    var getAll = await repo.GetAllAsync();
                }
            }
        }
    }
}
