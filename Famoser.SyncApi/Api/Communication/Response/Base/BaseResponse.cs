using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Base
{
    public class BaseResponse
    {
        public ApiError ApiError { get; set; }
        public string ServerMessage { get; set; }
        public bool RequestFailed { get; set; }
    }
}
