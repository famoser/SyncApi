using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Information;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IPersistentRespository<T> : IBasePersistentRepository
    {
        Task<T> GetAsync();
        Task<bool> SaveAsync();
        Task<bool> RemoveAsync();
        CacheInformations GetCacheInformations();
    }
}
