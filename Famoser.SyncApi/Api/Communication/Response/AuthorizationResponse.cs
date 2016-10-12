using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Response.Base;

namespace Famoser.SyncApi.Api.Communication.Response
{
    public class AuthorizationResponse : BaseResponse
    {
        public UserEntity UserEntity { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
    }
}
