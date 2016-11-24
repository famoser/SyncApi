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

        private AuthenticationState _authenticationState;
        public AuthenticationState GetAuthenticationState()
        {
            return _authenticationState;
        }

        public void SetAuthenticationState(AuthenticationState authenticationState)
        {
            _authenticationState = authenticationState;
        }
    }
}
