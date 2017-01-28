using System;
using Famoser.FrameworkEssentials.Logging;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services
{
    public class ApiTraceService : IApiTraceService
    {
        public void TraceFailedRequest(object request, string link, string message)
        {
            //don't do shit
        }

        public void TraceSuccessfulRequest(object request, string link)
        {
            //don't do shit
        }

        public ISyncActionInformation CreateSyncActionInformation(SyncAction action)
        {
            return new SyncActionInformation(action);
        }

        public void LogException(Exception ex, object @from = null)
        {
            //ignore too
        }
    }
}
