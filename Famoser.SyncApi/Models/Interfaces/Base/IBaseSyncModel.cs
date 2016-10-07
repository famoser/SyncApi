using System;

namespace Famoser.SyncApi.Models.Interfaces.Base
{
    public interface IBaseSyncModel
    {
        Guid GetId();
        void SetId(Guid id);
    }
}
