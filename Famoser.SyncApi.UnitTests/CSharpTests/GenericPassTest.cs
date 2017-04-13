using Microsoft.VisualStudio.TestTools.UnitTesting;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.UnitTests.CSharpTests
{
    [TestClass]
    public class GenericPassTest
    {
        [TestMethod]
        public void TestPass()
        {
            var arr = new string[1];
            Assert.ThrowsException<ArrayTypeMismatchException>(() => SaveStuff(arr));
        }

        private void SaveStuff(object[] arr)
        {
            arr[0] = 0;
        }
    }
}
