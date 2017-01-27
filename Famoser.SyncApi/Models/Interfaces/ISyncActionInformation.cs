using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Models.Interfaces
{
    public interface ISyncActionInformation
    {
        void SetSyncActionResult(SyncActionError result);
        void SetSyncActionException(Exception exception);
    }
}
