using System.Threading.Tasks;
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
        /// <summary>
        /// get the roaming entity
        /// this object should be synced accross all devices of the same user
        /// 
        /// this object will be unique, each call must return the same instance
        /// </summary>
        /// <returns></returns>
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        /// <summary>
        /// save the roaming entity
        /// </summary>
        /// <returns></returns>
        Task<bool> SaveApiRoamingEntityAsync();
        /// <summary>
        /// delete the roaming file
        /// </summary>
        /// <returns></returns>
        Task<bool> EraseRoamingAndCacheAsync();

        /// <summary>
        /// get a file from cache. This cache will be unique on each device
        /// for each filename you must return the same instance
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="filename"></param>
        /// <returns></returns>
        Task<T> GetCacheEntityAsync<T>(string filename) where T : class, new();
        /// <summary>
        /// save the instance of the cached file
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <returns></returns>
        Task<bool> SaveCacheEntityAsync<T>() where T : class, new();
        /// <summary>
        /// delete the instance of the cached file
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <returns></returns>
        Task<bool> EraseCacheEntityAsync<T>() where T : class, new();
    }
}
