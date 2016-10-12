using System;
using Famoser.SyncApi.Api.Base;

namespace Famoser.SyncApi.Api.Communication.Entities
{
    public class SyncEntity : BaseEntity
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public Guid CollectionId { get; set; }
        public string Identifier { get; set; }
        public string GroupIdentifier { get; set; }
    }
}
