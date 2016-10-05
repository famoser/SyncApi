using System.Collections.Generic;

namespace Famoser.SyncApi.Entities.Storage
{
    public class ModelCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; } = new List<TModel>();
        public List<ModelInformation> ModelInformations { get; set; } = new List<ModelInformation>();
        public List<ModelInformation> CollectionInformations { get; set; } = new List<ModelInformation>();
    }
}
