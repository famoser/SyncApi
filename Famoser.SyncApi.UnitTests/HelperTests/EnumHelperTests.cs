using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.HelperTests
{
    [TestClass]
    public class EnumHelperTests
    {
        [TestMethod]
        public void TestAllSyncActionEnumValuesHaveDescription()
        {
            TestEnum<SyncAction>();
        }

        [TestMethod]
        public void TestAllSyncActionErrorEnumValuesHaveDescription()
        {
            TestEnum<SyncActionError>();
        }

        private void TestEnum<T>()
        {
            var responses = new List<string>();
            foreach (var item in Enum.GetValues(typeof(T)))
            {
                dynamic elem = (T) item;
                var resp = EnumHelper.EnumToString(elem);
                Assert.IsTrue(!responses.Contains(resp), "failed for message " +resp + " from " + item);
                responses.Add(resp);
            }
        }
    }
}
