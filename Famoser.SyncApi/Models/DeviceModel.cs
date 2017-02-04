using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Base;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class DeviceModel : BaseModel, IDeviceModel
    {
        public override string GetClassIdentifier()
        {
            return "device";
        }

        public AuthenticationState AuthenticationState { get; set; }
        public AuthenticationState GetAuthenticationState()
        {
            return AuthenticationState;
        }

        public void SetAuthenticationState(AuthenticationState authenticationState)
        {
            AuthenticationState = authenticationState;
        }
    }
}
