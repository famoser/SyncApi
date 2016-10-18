using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class CollectionEntityResponse : SyncEntityResponse
    {
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
