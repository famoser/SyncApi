namespace Famoser.SyncApi.Enums
{
    public enum OnlineAction
    {
        None = 0,
        Create = 1,
        Read = 2,
        Update = 3,
        Delete = 4,
        ConfirmVersion = 5,
        ConfirmAccess = 6,

        AccessGranted = 10,
        AccessDenied = 11
    }
}
