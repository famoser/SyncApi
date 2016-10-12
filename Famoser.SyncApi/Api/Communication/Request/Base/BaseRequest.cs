using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Base
{
    public class BaseRequest
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
        public int AuthorizationCode { get; set; }
    }
}
