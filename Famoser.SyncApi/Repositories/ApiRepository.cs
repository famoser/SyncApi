using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.FrameworkEssentials.Services.Interfaces.Storage;
using Famoser.SyncApi.Interfaces;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiRepository<TModel> : BaseHelper
    where TModel : ISyncModel
    {
        private readonly IModelManager<TModel> _modelManager;
        private readonly ICacheStorageService _cacheStorageService;
        private IApiConfiguration _apiConfiguration;

        public ApiRepository(IModelManager<TModel> modelManager, ICacheStorageService cacheStorageService, IApiConfiguration apiConfiguration)
        {
            _modelManager = modelManager;
            _cacheStorageService = cacheStorageService;
            _apiConfiguration = apiConfiguration;
        }

        public ObservableCollection<TModel> GetAll()
        {
            Initialize();

            return _modelManager.GetObservableCollection();
        }


        private string GetCacheFilePath()
        {
            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            return model.GetUniqeIdentifier() + ".json";
        }

        private bool _isInitialized;
        private static readonly AsyncLock _asyncLock = new AsyncLock();
        private Task Initialize()
        {
            return ExecuteSafe(async () =>
            {
                using (await _asyncLock.LockAsync())
                {
                    if (_isInitialized)
                        return;

                    _isInitialized = true;

                    try
                    {
                        var json = await _cacheStorageService.GetCachedTextFileAsync(GetCacheFilePath());
                        var models = JsonConvert.DeserializeObject<List<TModel>>(json);
                        foreach (var model in models)
                        {
                            _modelManager.Add(model);
                        }
                    }
                    catch
                    {
                        // ignored: no savegame or wrong savegame. does not matter ether way
                    }
                }
            });
        }

        private ApiClient _apiClient;

        private Task<ApiClient> GetApiClient()
        {
            return ExecuteSafe(async () =>
            {
                if (_apiClient != null)
                    return _apiClient;

                _apiClient = new ApiClient();


                return _apiClient;
            });
        }


        public Task<bool> Sync()
        {
            return ExecuteSafe(async () =>
            {
                await Initialize();




                return true;
            });
        }

        public Task<bool> Save(TModel model)
        {

        }

        public Task<bool> Remove(TModel model)
        {

        }
    }
}
