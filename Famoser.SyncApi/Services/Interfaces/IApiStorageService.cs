using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces
{
    /// <summary>
    /// This service provides the repositories with the entities from storage. 
    /// If the entity is not found, it must retrieve a new instance of the modle (never 0)
    /// It must always return the same instance of the entity
    /// </summary>
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
