using System;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiAuthenticationService
    {
        bool IsAuthenticated();
        Task<bool> TryAuthenticationAsync();

        Guid GetUserId();
        Guid GetDeviceId();
    }
}
