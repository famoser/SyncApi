using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    /// <summary>
    /// This service creates & authenticates a device against the api
    /// </summary>
    public interface IApiDeviceAuthenticationService
    {
        /// <summary>
        /// Get an authenticated device model
        /// </summary>
        /// <param name="apiRoamingEntity"></param>
        /// <returns></returns>
        Task<IDeviceModel> GetDeviceAsync(ApiRoamingEntity apiRoamingEntity);
    }
}
