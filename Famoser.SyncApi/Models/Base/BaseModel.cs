using System;
using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Base
{
    public abstract class BaseModel : IUniqueSyncModel
    {
        public Guid Id { get; set; }
        public Guid GetId()
        {
            return Id;
        }

        public void SetId(Guid id)
        {
            Id = id;
        }

        public abstract string GetClassIdentifier();
    }
}
