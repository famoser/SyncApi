using System;

namespace Famoser.SyncApi.Entities
{
    public class CollectionEntity
    {
        public Guid Id { get; set; }
        public Guid UserId { get; set; }
        public string Identifier { get; set; }
    }
}
