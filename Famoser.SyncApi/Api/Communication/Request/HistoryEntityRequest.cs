using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class HistoryEntityRequest : BaseRequest
    {
        public Guid Id { get; set; }
        public List<Guid> VersionIds { get; set; }
    }
}
