using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Entities.Storage.Cache
{
    public class ApiCacheEntity
    {
        public Guid UserId { get; set; }
        public Guid DeviceId { get; set; }
    }
}
