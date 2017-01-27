using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Enums
{
    [Flags]
    public enum VerificationOption
    {
        None = 0,
        CanAccessInternet = 1,
        IsAuthenticatedFully = 2
    }
}
