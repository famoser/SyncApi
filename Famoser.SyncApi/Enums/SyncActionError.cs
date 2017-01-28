namespace Famoser.SyncApi.Enums
{
    public enum SyncActionError
    {
        None,
        ExecutionFailed,
        RequestCreationFailed,
        RequestUnsuccessful,
        InitializationFailed,
        WebAccessDenied,
        NotAuthenticatedFully,
        LocalFileAccessFailed,
        EntityAlreadyRemoved
    }
}
