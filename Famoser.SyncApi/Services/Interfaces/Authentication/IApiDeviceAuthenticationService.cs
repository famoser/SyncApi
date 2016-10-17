using System;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiDeviceAuthenticationService
    {
        Task<Guid?> TryGetAuthenticatedDeviceIdAsync();
    }
}
