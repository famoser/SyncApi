using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Famoser.SyncApi.UnitTests.CSharpTests
{
    [TestClass]
    class LinqTests
    {
        private readonly Dictionary<Type, List<Tuple<Type, int>>> _internal = new Dictionary<Type, List<Tuple<Type, int>>>();


        public void TestSelectMany()
        {
            //get list with all int
            IEnumerable<int> res = _internal.SelectMany(s => s.Value.Select(e => e.Item2));
        }
    }
}
