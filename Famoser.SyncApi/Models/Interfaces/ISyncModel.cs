using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Interfaces
{
    /// <summary>
    /// implement this interface in your models to be synced.
    /// Mark all properties to be synced with the EntityMap attribute
    /// </summary>
    public interface ISyncModel : IUniqueSyncModel
    {

    }
}
