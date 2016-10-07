using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiStorageService
    {
        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        ApiRoamingEntity GetApiRoamingEntity();
        Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity);

        Task<ApiCacheEntity> GetApiCacheEntityAsync();
        ApiCacheEntity GetApiCacheEntity();
        Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity);

        Task<ModelCacheEntity<TModel>> GetModelCacheAsync<TModel>(string identifier) where TModel : ISyncModel;
        ModelCacheEntity<TModel> GetModelCache<TModel>(string identifier) where TModel : ISyncModel;
        Task<bool> SetModelCacheJsonAsync<TModel>(string identifier, ModelCacheEntity<TModel> cache) where TModel : ISyncModel;
    }
}
