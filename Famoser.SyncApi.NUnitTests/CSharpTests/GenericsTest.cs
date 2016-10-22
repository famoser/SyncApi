using Famoser.SyncApi.NUnitTests.CSharpTests.Models;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.CSharpTests
{
    [TestFixture]
    public class GenericsTest
    {
        [Test]
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
