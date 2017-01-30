using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    /// <summary>
    /// This service creates & authenticates a user against the api
    /// </summary>
    public interface IApiUserAuthenticationService
    {
        /// <summary>
        /// Get the api roaming entity
        /// This method will probably execute one or more requests against the api to create a new user if none exists already
        /// </summary>
        /// <returns></returns>
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();

        /// <summary>
        /// Set the authentication service
        /// </summary>
        /// <param name="apiAuthenticationService"></param>
        void SetAuthenticationService(IApiAuthenticationService apiAuthenticationService);
    }
}
