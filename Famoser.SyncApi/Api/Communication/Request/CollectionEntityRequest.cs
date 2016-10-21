using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class CollectionEntityRequest : SyncEntityRequest
    {
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
