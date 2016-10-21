using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class SyncEntityRequest : CollectionEntityRequest
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
    }
}
