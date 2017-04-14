using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Response.Base;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class DeviceEntityResponse : BaseResponse
    {
        //todo: rename in api to DeviceEntities
        //todo: use DeviceEntity in api
        public List<DeviceEntity> CollectionEntities { get; set; } = new List<DeviceEntity>();
    }
}
