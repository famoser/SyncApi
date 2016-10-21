using System;
using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Base
{
    public abstract class BaseModel : IUniqueSyncModel
    {
        private Guid _userId;
        public Guid GetId()
        {
            return _userId;
        }

        public void SetId(Guid id)
        {
            _userId = id;
        }

        public abstract string GetUniqeIdentifier();
    }
}
