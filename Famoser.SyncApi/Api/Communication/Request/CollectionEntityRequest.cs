using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class CollectionEntityRequest : BaseRequest
    {
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
