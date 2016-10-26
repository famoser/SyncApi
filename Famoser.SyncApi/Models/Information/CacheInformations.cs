using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Models.Information
{
    public class CacheInformations
    {
        public Guid Id { get; set; }
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public Guid CollectionId { get; set; }
        public Guid VersionId { get; set; }
        public DateTime CreateDateTime { get; set; }
        public PendingAction PendingAction { get; set; }
        

        public Guid CreateVersionId()
        {
            VersionId = Guid.NewGuid();
            return VersionId;
        }
    }
}
