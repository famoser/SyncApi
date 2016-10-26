using System;

namespace Famoser.SyncApi.Models.Information
{
    public class HistoryInformations<TModel>
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public Guid CollectionId { get; set; }
        public Guid VersionId { get; set; }
        public DateTime CreateDateTime { get; set; }
        public TModel Model { get; set; }
    }
}
