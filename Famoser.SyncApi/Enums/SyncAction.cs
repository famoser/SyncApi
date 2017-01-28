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
        GetUser,
        SaveUser,
        FoundUser,
        SyncUser,
        RemoveUser,

        GetDevice,
        CreateDevice,
        FoundDevice,
        SyncDevice,
        SaveDevice,
        AuthenticateDevice,
        UnAuthenticateDevice,
        CreateAuthCode,
        UseAuthCode,
        RemoveDevice,

        GetAllDevices,

        GetCollections,
        GetCollectionsAsync,
        GetCollectionHistory,
        SyncCollectionHistory,
        CreateCollection,
        SyncCollection,
        SaveCollection,
        RemoveCollection,
        AddUserToCollection,

        GetDefaultCollection,

        GetEntities,
        GetEntityHistory,
        SyncEntityHistory,
        GetEntitiesAsync,
        SaveEntity,
        RemoveEntity,
        SyncEntity
    }
}
