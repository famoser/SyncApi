using System;
using System.Collections.Generic;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities
{
    public class RequestEntity
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
        public List<CollectionEntity> CollectionEntities { get; set; } = new List<CollectionEntity>();
        public List<UserEntity> UserEntities { get; set; } = new List<UserEntity>();
        public List<DeviceEntity> DeviceEntities { get; set; } = new List<DeviceEntity>();
        public OnlineAction OnlineAction { get; set; }
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
    }
}
