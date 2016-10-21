using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiDeviceAuthenticationService
    {
        /// <summary>
        /// Get a DeviceId
        /// </summary>
        /// <param name="apiRoamingEntity"></param>
        /// <returns></returns>
        Task<Guid?> GetAuthenticatedDeviceIdAsync(ApiRoamingEntity apiRoamingEntity);
    }
}
