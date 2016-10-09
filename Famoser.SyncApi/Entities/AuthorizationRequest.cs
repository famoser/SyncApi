using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities
{
    public class AuthorizationRequest
    {
        public UserEntity UserEntity { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
        public OnlineAction OnlineAction { get; set; }
        public Guid UserId { get; set; }
    }
}
