using System.Collections.Generic;
using Famoser.SyncApi.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Storage.Cache
{
    public class CollectionCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; }
        public List<ModelInformation> ModelInformations { get; set; }
    }
}
