using System;

namespace Famoser.SyncApi.Entities.Storage.Cache.Entitites
{
    public class CollectionInformations
    {
        public string GroupIdentifier { get; set; }
        public Guid CollectionId { get; set; }
        public bool IsPrimary { get; set; }
        public bool IsWriteDisabled { get; set; }
    }
}
