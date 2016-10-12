using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Interfaces
{
    public interface IDeviceModel : IBaseSyncModel
    {
        AuthenticationState GetAuthenticationState();
        void SetAuthenticationState(AuthenticationState authenticationState);
    }
}
