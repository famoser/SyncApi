using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request.Base;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiAuthenticationService
    {
        bool IsAuthenticated();
        Task<bool> AuthenticateAsync();

        bool AuthenticateRequest(BaseRequest request);
    }
}
