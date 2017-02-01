using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiTraceService : IExceptionLogger
    {
        ISyncActionInformation CreateSyncActionInformation(SyncAction action);
        void TraceSuccessfulRequest(BaseRequest request, string link);
        void TraceFailedRequest(BaseRequest request, string link, string message);
    }
}
