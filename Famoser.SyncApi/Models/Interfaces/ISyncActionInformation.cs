using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Models.Interfaces
{
    public interface ISyncActionInformation
    {
        void SetSyncActionResult(SyncActionError result);
        void SetSyncActionException(Exception exception);
    }
}
