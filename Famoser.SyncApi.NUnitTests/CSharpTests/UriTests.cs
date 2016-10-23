using System;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.CSharpTests
{
    [TestFixture]
    public class UriTests
    {
        [Test]
        public void TestAbsoluteUrl()
        {
            var uri = new Uri("http://google.com");
            Assert.IsTrue(uri.AbsoluteUri == "http://google.com/");
        }
    }
}
