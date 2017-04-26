using Microsoft.VisualStudio.TestTools.UnitTesting;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.UnitTests.CSharpTests
{
    [TestClass]
    public class GenericCastTest
    {
        interface IModel { }

        class MyModel : IModel { }

        interface IImplementation<TModel> where TModel : IModel { }

        class MyImplementation<TModel> : IImplementation<TModel>
            where TModel : IModel
        { }

        [TestMethod]
        public void CallRegister()
        {
            var implementation = new MyImplementation<MyModel>();
            var instance = Register(implementation);
            Assert.IsNull(instance);
        }

        private object Register<TModel>(IImplementation<TModel> implementation) where TModel : IModel
        {
            return implementation as IImplementation<IModel>;
        }
    }
}
