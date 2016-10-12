using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Base;

namespace Famoser.SyncApi.Entities
{
    public class AuthorizationResponse : BaseResponse
    {
        public UserEntity UserEntity { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
    }
}
