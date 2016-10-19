using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiAuthenticationService
    {
        bool IsAuthenticated();
        Task<bool> AuthenticateAsync();

        bool AuthenticateRequest(BaseRequest request);
        bool FillModelInformation(ModelInformation info);
    }
}
