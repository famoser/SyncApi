using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiStorageService
    {
        Task<bool> InitializeAsync();

        ApiRoamingEntity GetApiRoamingEntity();
        ApiCacheEntity GetApiCacheEntity();
        Task SaveApiCacheEntityAsync();
        
        ModelCacheEntity<TModel> GetModelCache<TModel>(string identifier) where TModel : ISyncModel;
        Task SetModelCacheAsync<TModel>(string identifier, ModelCacheEntity<TModel> cache) where TModel : ISyncModel;
    }
}
