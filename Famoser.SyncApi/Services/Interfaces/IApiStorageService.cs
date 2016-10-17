using System.Threading.Tasks;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiStorageService
    {
        Task<ApiRoamingEntity> GetApiRoamingEntity();
        Task<bool> SaveApiRoamingEntityAsync();
        Task<bool> EraseRoamingAndCacheAsync();

        Task<CacheEntity<T>> GetCacheEntity<T>();
        Task<bool> SaveCacheEntityAsync<T>();
        Task<bool> EraseCacheEntityAsync<T>();

        Task<CollectionCacheEntity<T>> GetCollectionCacheEntity<T>();
        Task<bool> SaveCollectionEntityAsync<T>();
        Task<bool> EraseCollectionEntityAsync<T>();
    }
}
