using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class SyncEntityRequest : BaseRequest
    {
        public List<SyncEntity> SyncEntities { get; set; } = new List<SyncEntity>();
    }
}
