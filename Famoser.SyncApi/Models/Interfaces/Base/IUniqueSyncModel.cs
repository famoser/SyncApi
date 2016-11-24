namespace Famoser.SyncApi.Models.Interfaces.Base
{
    public interface IUniqueSyncModel : IBaseSyncModel
    {
        /// <summary>
        /// Get unique identifier (different for all classes) for the API
        /// </summary>
        /// <returns></returns>
        string GetClassIdentifier();
    }
}
