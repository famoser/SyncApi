using System;
using System.Collections.ObjectModel;
using Famoser.FrameworkEssentials.Logging;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Events;
using Famoser.SyncApi.Models;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services
{
    public class ApiTraceService : IApiTraceService
    {
        public ObservableCollection<SyncActionInformation> SyncActionInformations { get; } = new ObservableCollection<SyncActionInformation>();

        public EventHandler<RequestEventArgs> RequestFailed;
        public EventHandler<RequestEventArgs> RequestSuccessful;
        public void TraceFailedRequest(BaseRequest request, string link, string message)
        {
            RequestFailed?.Invoke(this, new RequestEventArgs(request, link, false,message));
        }

        public void TraceSuccessfulRequest(BaseRequest request, string link)
        {
            RequestSuccessful?.Invoke(this, new RequestEventArgs(request, link));
        }

        public ISyncActionInformation CreateSyncActionInformation(SyncAction action)
        {
            var info = new SyncActionInformation(action);
            SyncActionInformations.Add(info);
            return info;
        }

        public void LogException(Exception ex, object @from = null)
        {
            //ignore
        }
    }
}
