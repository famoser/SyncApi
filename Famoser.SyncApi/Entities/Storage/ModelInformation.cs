using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Entities.Storage
{
    internal class ModelInformation
    {
        public Guid Id { get; set; }
        public Guid CollectionId { get; set; }
        public PendingAction PendingAction { get; set; }
    }
}
