using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiUserAuthenticationService
    {
        Task<ApiRoamingEntity> TryGetApiRoamingEntityAsync();
    }
}
