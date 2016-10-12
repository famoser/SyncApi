using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class CollectionCacheEntity<TCollection>
        where TCollection : ICollectionModel
    {
        public TCollection CollectionModel { get; set; }
        public ModelInformation CollectionInformation { get; set; }
    }
}
