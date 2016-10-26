using System.Collections.Generic;
using Famoser.SyncApi.Models.Information;

namespace Famoser.SyncApi.Storage.Cache
{
    public class CollectionCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; } = new List<TModel>();
        public List<CacheInformations> ModelInformations { get; set; } = new List<CacheInformations>();
    }
}
