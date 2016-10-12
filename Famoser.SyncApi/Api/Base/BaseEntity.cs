using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Api.Base
{
    public class BaseEntity
    {
        public Guid Id { get; set; }
        public Guid VersionId { get; set; }
        public OnlineAction OnlineAction { get; set; }
        public string Content { get; set; }
    }
}
