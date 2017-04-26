using System;

namespace Famoser.SyncApi.Enums
{
    [Flags]
    public enum VerificationOption
    {
        None = 0,
        CanAccessInternet = 1,
        IsAuthenticatedFully = 2,
        AuthenticateBeforeInitialize = 4
    }
}
