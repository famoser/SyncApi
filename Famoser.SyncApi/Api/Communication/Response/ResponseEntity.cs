using System.Collections.Generic;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Base;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities
{
    public class ResponseEntity : BaseResponse
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
