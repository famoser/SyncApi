using System.Collections.Generic;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ModelCacheEntity<TModel, TCollection>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
    {
        public List<TModel> Models { get; set; } = new List<TModel>();
        public List<ModelInformation> ModelInformations { get; set; } = new List<ModelInformation>();
        public TCollection CollectionModel { get; set; }
        public ModelInformation CollectionInformation { get; set; }
    }
}
