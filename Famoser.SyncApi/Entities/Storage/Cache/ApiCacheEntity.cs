using System;
using System.Collections.Generic;
using System.Linq;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ApiCacheEntity<TDevice, TUser> 
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        public Guid DeviceId { get; set; }
        public TDevice DeviceModel { get; set; }
        public ModelInformation DeviceModelInformation { get; set; }
        public TUser UserModel { get; set; }
        public ModelInformation UserModelInformation { get; set; }
        public List<CollectionInformations> CollectionInformations { get; set; } = new List<CollectionInformations>();
        public List<string> ModelIdentifiers { get; set; }

        public CollectionInformations GetSaveCollection(string groupIdentifier)
        {
            return CollectionInformations.FirstOrDefault(c => c.CanSave && c.GroupIdentifier == groupIdentifier);
        }
    }
}
