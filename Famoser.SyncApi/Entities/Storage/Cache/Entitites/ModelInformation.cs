using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Storage.Cache.Entitites
{
    public class ModelInformation
    {
        public Guid Id { get; set; }
        public Guid VersionId { get; set; }
        public Guid CollectionId { get; set; }
        public PendingAction PendingAction { get; set; }
    }
}
