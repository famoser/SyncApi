using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiStorageService
    {
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity);

        Task<ApiCacheEntity> GetApiCacheEntityAsync();
        Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity);

        Task<string> GetModelCacheJsonAsync(string identifier);
        Task<bool> SetModelCacheJsonAsync(string identifier, string json);
    }
}
