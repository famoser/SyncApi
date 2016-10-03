using System;
using Famoser.SyncApi.Entities.Api.Base;

namespace Famoser.SyncApi.Entities.Api
{
    public class CollectionEntity : BaseEntity
    {
        public Guid UserId { get; set; }
        public string Identifier { get; set; }
    }
}
