using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class CollectionEntityResponse : SyncEntityResponse
    {
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
