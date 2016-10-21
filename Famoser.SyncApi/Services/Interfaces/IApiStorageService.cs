using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Cache;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces
{
    /// <summary>
    /// This service provides the repositories with the entities from storage. 
    /// If the entity is not found, it must retrieve a new instance of the modle (never null, also do NOT throw exceptions. As it is only a caching service, it is not usefull anyways)
    /// It must always return the same instance of the entity
    /// </summary>
    public interface IApiStorageService
    {
        Task<ApiRoamingEntity> GetApiRoamingEntity();
        Task<bool> SaveApiRoamingEntityAsync();
        Task<bool> EraseRoamingAndCacheAsync();

        Task<CacheEntity<T>> GetCacheEntity<T>(string filename);
        Task<bool> SaveCacheEntityAsync<T>();
        Task<bool> EraseCacheEntityAsync<T>();

        Task<CollectionCacheEntity<T>> GetCollectionCacheEntity<T>(string filename);
        Task<bool> SaveCollectionEntityAsync<T>();
        Task<bool> EraseCollectionEntityAsync<T>();
    }
}
