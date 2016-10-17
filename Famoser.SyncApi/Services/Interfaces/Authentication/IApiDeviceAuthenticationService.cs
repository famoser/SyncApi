using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiDeviceAuthenticationService
    {
        Task<Guid?> TryGetAuthenticatedDeviceIdAsync(ApiRoamingEntity apiRoamingEntity);
    }
}
