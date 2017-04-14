using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class DeviceEntityRequest : BaseRequest
    {
        public List<DeviceEntity> CollectionEntities { get; set; } = new List<DeviceEntity>();
    }
}
