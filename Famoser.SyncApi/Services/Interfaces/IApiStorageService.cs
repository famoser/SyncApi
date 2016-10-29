using System.Threading.Tasks;
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
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        Task<bool> SaveApiRoamingEntityAsync();
        Task<bool> EraseRoamingAndCacheAsync();

        Task<T> GetCacheEntityAsync<T>(string filename) where T : class, new();
        Task<bool> SaveCacheEntityAsync<T>() where T : class, new();
        Task<bool> EraseCacheEntityAsync<T>() where T : class, new();
    }
}
