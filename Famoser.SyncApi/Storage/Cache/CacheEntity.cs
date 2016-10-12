using Famoser.SyncApi.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Storage.Cache
{
    public class CacheEntity<TModel>
    {
        public TModel Model { get; set; }
        public ModelInformation ModelInformation { get; set; }
    }
}
