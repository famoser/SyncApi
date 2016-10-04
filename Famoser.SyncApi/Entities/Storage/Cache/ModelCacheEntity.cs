using System.Collections.Generic;

namespace Famoser.SyncApi.Entities.Storage
{
    internal class ModelCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; } = new List<TModel>();
        public List<ModelInformation> ModelInformations { get; set; } = new List<ModelInformation>();
    }
}
