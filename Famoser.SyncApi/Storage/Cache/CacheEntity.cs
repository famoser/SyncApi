using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class CacheEntity<TModel>
    {
        public TModel Model { get; set; }
        public ModelInformation ModelInformation { get; set; }
    }
}
