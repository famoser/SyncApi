using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Interfaces
{
    public interface IDeviceModel : IUniqueSyncModel
    {
        AuthenticationState GetAuthenticationState();
        void SetAuthenticationState(AuthenticationState authenticationState);
    }
}
