using System;
using Famoser.SyncApi.Api.Communication.Entities.Base;

namespace Famoser.SyncApi.Api.Communication.Entities
{
    public class DeviceEntity : BaseEntity
    {
        public Guid UserId { get; set; }
    }
}
