using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Api.Communication.Entities.Base
{
    public class BaseEntity
    {
        public Guid Id { get; set; }
        public Guid VersionId { get; set; }
        public OnlineAction OnlineAction { get; set; }
        public string Content { get; set; }
        public DateTime CreateDateTime { get; set; }
        public string Identifier { get; set; }
        public bool IsDeleted { get; set; }
    }
}
