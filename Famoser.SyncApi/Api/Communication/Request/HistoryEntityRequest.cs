using System;
using System.Collections.Generic;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Api.Communication.Request
{
    public class HistoryEntityRequest : BaseRequest
    {
        public Guid Id { get; set; }
        public List<Guid> VersionIds { get; set; }
    }
}
