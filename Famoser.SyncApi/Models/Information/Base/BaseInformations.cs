using System;

namespace Famoser.SyncApi.Models.Information.Base
{
    public class BaseInformations
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public Guid CollectionId { get; set; }
        public Guid VersionId { get; set; }
        public DateTime CreateDateTime { get; set; }
    }
}
