using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Entities.Storage.Cache.Base
{
    public class CollectionCacheEntity<TModel>
    {
        public List<TModel> Models { get; set; }
        public List<ModelInformation> ModelInformations { get; set; }
    }
}
