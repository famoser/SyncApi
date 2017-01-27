using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Enums
{
    public enum SyncAction
    {
        CreateUser,
        FoundUser,
        SyncUser,

        CreateDevice,
        FoundDevice,
        SyncDevice,
        AuthenticateDevice,
        UnAuthenticateDevice,
        CreateAuthCode,
        UseAuthCode,

        GetAllDevices,

        CreateCollection,
        SyncCollection,
        SaveCollection,
        RemoveCollection,
        AddUserToCollection,

        GetDefaultCollection,

        SaveEntity,
        RemoveEntity,
        SyncEntity
    }
}
