using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Api.Communication.Response.Base
{
    public class BaseResponse
    {
        public ApiError ApiError { get; set; }
        public string ServerMessage { get; set; }
        public bool RequestFailed { get; set; }
        public bool IsSuccessfull => !RequestFailed;
    }
}
