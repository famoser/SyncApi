using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Response.Base;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class ResponseEntity : BaseResponse
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
    }
}
