﻿using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Tests.CSharpTests.Models;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.Tests.CSharpTests
{
    [TestClass]
    public class GenericsTest
    {
        [TestMethod]
        public void TestInheritanceBehaviour()
        {
            var model = new ChildModel();
            Assert.IsTrue(DoStuff(model) == 1);

            BaseModel mod = model;
            Assert.IsTrue(DoStuff(mod) == 2);
            
            Assert.IsTrue(GenericStuff(model) == 2);
        }

        private int DoStuff(ChildModel model)
        {
            return 1;
        }

        private int DoStuff(BaseModel model)
        {
            return 2;
        }

        private int GenericStuff<T>(T modl) where T : BaseModel
        {
            return DoStuff(modl);
        }
    }
}
