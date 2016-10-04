using System;
using Famoser.SyncApi.Entities.Api.Base;

namespace Famoser.SyncApi.Entities.Api
{
    public class UserEntity : BaseEntity
    {
        public DateTime CreateDateTime { get; set; }
        public DateTime LastRequestDateTime { get; set; }
    }
}
