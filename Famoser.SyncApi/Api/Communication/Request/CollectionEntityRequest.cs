using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class CollectionEntityRequest : SyncEntityRequest
    {
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
