using System;
using Famoser.SyncApi.Api.Base;

namespace Famoser.SyncApi.Api.Communication.Entities
{
    public class SyncEntity : CollectionEntity
    {
        public Guid CollectionId { get; set; }
    }
}
