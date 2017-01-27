using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Enums
{
    public enum SyncActionError
    {
        None,
        ExecutionFailed,
        RequestCreationFailed,
        RequestUnsuccessful,
        InitializationFailed,
        WebAccessDenied,
        NotAuthenticatedFully,
        LocalFileAccessFailed,
        EntityAlreadyRemoved
    }
}
