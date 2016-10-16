using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiStorageService
    {
        Task<bool> EraseAllAsync();
        Task<ApiRoamingEntity> GetApiRoamingEntity();
        Task<bool> SaveApiRoamingEntityAsync();
        
        Task<CacheEntity<T>> GetCacheEntity<T>();
        Task<bool> SaveCacheEntityAsync<T>();

        Task<CollectionCacheEntity<T>> GetCollectionCacheEntity<T>();
        Task<bool> SaveCollectionEntityAsync<T>();
    }
}
