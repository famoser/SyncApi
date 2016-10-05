using System.Collections.Generic;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ModelCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; } = new List<TModel>();
        public List<ModelInformation> ModelInformations { get; set; } = new List<ModelInformation>();
        public List<ModelInformation> CollectionInformations { get; set; } = new List<ModelInformation>();
    }
}
