using System;
using Famoser.SyncApi.Entities.Api;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ApiCacheEntity
    {
        public Guid DeviceId { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
        public UserEntity UserEntity { get; set; }

    }
}
