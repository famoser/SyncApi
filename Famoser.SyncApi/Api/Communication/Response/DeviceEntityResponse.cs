using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Response.Base;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class DeviceEntityResponse : BaseResponse
    {
        public List<DeviceEntity> DeviceEntities { get; set; } = new List<DeviceEntity>();
    }
}
