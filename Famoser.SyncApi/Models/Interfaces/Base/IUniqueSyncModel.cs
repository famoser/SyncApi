namespace Famoser.SyncApi.Models.Interfaces.Base
{
    public interface IUniqueSyncModel : IBaseSyncModel
    {
        /// <summary>
        /// Get unique identifier (different for all objects) for the API
        /// </summary>
        /// <returns></returns>
        string GetUniqeIdentifier();
    }
}
