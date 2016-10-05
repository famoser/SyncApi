using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Services;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel>
    where TModel : ISyncModel
    {
        private string _modelCacheFilePath;
        private string GetModelCacheFilePath()
        {
            if (_modelCacheFilePath == null)
                return _modelCacheFilePath;

            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            _modelCacheFilePath = _apiConfigurationService.GetFileName(model.GetUniqeIdentifier() + ".json", typeof(TModel));

            return _modelCacheFilePath;
        }

        private ApiClient<TModel> _apiClient;
        private ApiClient<TModel> GetApiClient()
        {
            if (_apiClient != null)
                return _apiClient;

            _apiClient = new ApiClient<TModel>(_apiConfigurationService.GetApiUri(), _apiStorageService.GetApiRoamingEntity().UserId);
            return _apiClient;
        }

        private ApiAuthorizationHelper<TModel> _helper;
        private ApiAuthorizationHelper<TModel> GetApiAuthorizationHelper()
        {
            if (_helper != null)
                return _helper;

            _helper = new ApiAuthorizationHelper<TModel>(GetApiClient(), _apiConfigurationService, _apiStorageService);
            return _helper;
        }

        private ModelCacheEntity<TModel> GetModelCache()
        {
            return _apiStorageService.GetModelCache<TModel>(GetModelCacheFilePath());
        }

        private ApiCacheEntity GetApiCache()
        {
            return _apiStorageService.GetApiCacheEntity();
        }

        private ApiRoamingEntity GetApiRoaming()
        {
            return _apiStorageService.GetApiRoamingEntity();
        }
    }
}
