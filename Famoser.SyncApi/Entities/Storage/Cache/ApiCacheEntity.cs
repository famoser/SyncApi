using System;
using System.Collections.Generic;
using System.Linq;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ApiCacheEntity
    {
        public Guid DeviceId { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
        public UserEntity UserEntity { get; set; }
        public List<CollectionInformations> CollectionInformations { get; set; } = new List<CollectionInformations>();
        public List<string> ModelIdentifiers { get; set; }

        public CollectionInformations GetSaveCollection(string groupIdentifier)
        {
            return CollectionInformations.FirstOrDefault(c => c.CanSave && c.GroupIdentifier == groupIdentifier);
        }
    }
}
