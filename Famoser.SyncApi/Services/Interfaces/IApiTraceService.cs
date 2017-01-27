using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiTraceService
    {
        ISyncActionInformation CreateSyncActionInformation(SyncAction action);
        void TraceSuccessfulRequest(object request, string link);
        void TraceFailedRequest(object request, string link, string message);
    }
}
