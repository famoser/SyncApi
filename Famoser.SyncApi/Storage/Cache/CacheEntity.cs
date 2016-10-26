using Famoser.SyncApi.Models.Information;

namespace Famoser.SyncApi.Storage.Cache
{
    public class CacheEntity<TModel>
    {
        public TModel Model { get; set; }
        public CacheInformations ModelInformation { get; set; }
    }
}
