using System;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public abstract class AbstractSyncModel : ISyncModel
    {
        private Guid _id;
        public void SetId(Guid id)
        {
            _id = id;
        }

        public Guid GetId()
        {
            return _id;
        }

        public abstract string GetClassIdentifier();
    }
}
