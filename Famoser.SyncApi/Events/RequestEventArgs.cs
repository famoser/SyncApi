using System;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Events
{
    public class RequestEventArgs : EventArgs
    {
        public RequestEventArgs(BaseRequest request, string link, bool isSuccessful = true, string message = null)
        {
            Request = request;
            Link = link;
            Message = message;
            IsSuccessful = isSuccessful;
        }

        public BaseRequest Request { get; }
        public string Link { get; }
        public string Message { get; }
        public bool IsSuccessful { get; }
    }

}
