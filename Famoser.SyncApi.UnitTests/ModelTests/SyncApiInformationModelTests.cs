using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.UnitTests.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.ModelTests
{
    [TestClass]
    public class SyncApiInformationModelTests
    {
        [TestMethod]
        public void CheckDescription()
        {
            //arrange
            var str = EnumHelper.EnumToString(SyncAction.CreateDevice);
            var sam = new SyncActionInformation(SyncAction.CreateDevice);
            Assert.IsTrue(sam.FullDescription.Contains("..."));
            Assert.IsTrue(sam.FullDescription.Contains(str));

            var errorStr = EnumHelper.EnumToString(SyncAction.CreateDevice);
            sam.SetSyncActionResult(SyncActionError.LocalFileAccessFailed);
            Assert.IsTrue(sam.FullDescription.Contains("failed"));
            Assert.IsTrue(sam.FullDescription.Contains(str));
            Assert.IsTrue(sam.FullDescription.Contains(errorStr));

            sam.SetSyncActionResult(SyncActionError.None);
            Assert.IsTrue(!sam.FullDescription.Contains("..."));
            Assert.IsTrue(sam.FullDescription.Contains(str));
        }
    }
}
