using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiUserAuthenticationService
    {
        /// <summary>
        /// Get Api informations
        /// </summary>
        /// <returns></returns>
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
    }
}
