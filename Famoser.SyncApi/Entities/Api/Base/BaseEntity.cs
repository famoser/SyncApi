using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Api.Base
{
    public class BaseEntity
    {
        public Guid Id { get; set; }
        public Guid VersionId { get; set; }
        public OnlineAction OnlineAction { get; set; }
        public string Content { get; set; }
    }
}
