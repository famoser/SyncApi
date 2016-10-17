using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class AuthRequestEntity : BaseRequest
    {
        public UserEntity UserEntity { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
        public string ClientMessage { get; set; }
    }
}
