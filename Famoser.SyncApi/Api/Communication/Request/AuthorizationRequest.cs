using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Base;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities
{
    public class AuthRequestEntity : BaseRequest
    {
        public UserEntity UserEntity { get; set; }
        public DeviceEntity DeviceEntity { get; set; }
    }
}
