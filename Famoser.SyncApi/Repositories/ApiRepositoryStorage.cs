using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel>
    where TModel : ISyncModel
    {
        private async Task<bool> InitializeFromStorageAsync()
        {
            try
            {
                var json = await _storageService.GetRoamingTextFileAsync(GetApiRoamingFilePath());
                _apiRoamingEntity = JsonConvert.DeserializeObject<ApiRoamingEntity>(json);

                json = await _storageService.GetCachedTextFileAsync(GetApiCacheFilePath());
                _apiCacheEntity = JsonConvert.DeserializeObject<ApiCacheEntity>(json);

                //read out storage
                json = await _storageService.GetCachedTextFileAsync(GetModelCacheFilePath());
                _apiCacheModel = JsonConvert.DeserializeObject<ModelCacheEntity<TModel>>(json);
                foreach (var model in _apiCacheModel.Models)
                {
                    _modelManager.Add(model);
                }
            }
            catch
            {
                // exception ignored: no savegame or wrong savegame. does not matter either way
            }
            return true;
        }

        private string GetModelCacheFilePath()
        {
            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            return _apiConfiguration.GetFileName(model.GetUniqeIdentifier() + ".json", typeof(TModel));
        }

        private string GetApiCacheFilePath()
        {
            return _apiConfiguration.GetFileName("api_cache.json");
        }

        private string GetApiRoamingFilePath()
        {
            return _apiConfiguration.GetFileName("api_roaming.json");
        }
    }
}
