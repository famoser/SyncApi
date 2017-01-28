using System;
using System.Net;
using System.Threading.Tasks;
using Famoser.SyncApi.Models;
using NUnit.Framework;

namespace Famoser.SyncApi.NUnitTests.CSharpTests
{
    [TestFixture]
    public class AsyncTests
    {
        public async Task CheckForSameInstance()
        {
            var instance = await GetInstanceAsync();
            Assert.IsTrue(instance.Item1 == _collectionModel);


            var instance2 = await GetInstanceAsync();
            Assert.IsTrue(instance2.Item1 == _collectionModel);
        }

        private CollectionModel _collectionModel;
        private async Task<Tuple<CollectionModel, byte[]>> GetInstanceAsync()
        {
            using (var client = new WebClient())
            {
                var res = await client.DownloadDataTaskAsync(new Uri("https://www.google.ch/?gws_rd=ssl"));
                return new Tuple<CollectionModel, byte[]>(_collectionModel, res);
            }
        }
    }
}
