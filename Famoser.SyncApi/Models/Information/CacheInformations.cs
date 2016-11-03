using System;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Information.Base;

namespace Famoser.SyncApi.Models.Information
{
    public class CacheInformations : BaseInformations
    {
        public Guid Id { get; set; }
        public PendingAction PendingAction { get; set; }
        
        public Guid CreateVersionId()
        {
            VersionId = Guid.NewGuid();
            return VersionId;
        }
    }
}
