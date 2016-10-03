using System;
using Famoser.SyncApi.Entities.Api.Base;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Api
{
    public class SyncEntity : BaseEntity
    {
        public Guid CollectionId { get; set; }
        public string Identifier { get; set; }
        public string GroupIdentifier { get; set; }
    }
}
