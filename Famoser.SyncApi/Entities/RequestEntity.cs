using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities
{
    public class RequestEntity
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
        public OnlineAction OnlineAction { get; set; }
        public Guid UserId { get; set; }
    }
}
