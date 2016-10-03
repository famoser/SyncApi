using System;

namespace Famoser.SyncApi.Entities.Api
{
    public class SyncEntity
    {
        public Guid Id { get; set; }
        public Guid CollectionId { get; set; }
        public string Content { get; set; }
        public string Identifier { get; set; }
        public string GroupIdentifier { get; set; }
    }
}
