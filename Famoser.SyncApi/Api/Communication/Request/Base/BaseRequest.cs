using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Api.Communication.Request.Base
{
    public class BaseRequest
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public string AuthorizationCode { get; set; }
        public OnlineAction OnlineAction { get; set; }
    }
}
