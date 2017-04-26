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
        CheckAuthentication,

        GetCollections,
        GetCollectionHistory,
        SyncCollectionHistory,
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
        SyncEntities
    }
}
