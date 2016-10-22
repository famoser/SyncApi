using System;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.Tests.CSharpTests
{
    [TestClass]
    public class UriTests
    {
        [TestMethod]
        public void TestAbsoluteUrl()
        {
            var uri = new Uri("http://google.com");
            Assert.IsTrue(uri.AbsoluteUri == "http://google.com/");
        }
    }
}
