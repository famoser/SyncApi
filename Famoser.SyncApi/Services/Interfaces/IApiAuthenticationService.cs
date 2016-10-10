using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
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
