using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using System;
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
    }
}
