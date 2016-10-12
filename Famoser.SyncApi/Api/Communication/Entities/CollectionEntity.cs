using System;
using Famoser.SyncApi.Api.Base;

namespace Famoser.SyncApi.Api.Communication.Entities
{
    public class CollectionEntity : BaseEntity
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public string Identifier { get; set; }
    }
}
