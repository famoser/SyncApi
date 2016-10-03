using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.FrameworkEssentials.Services.Interfaces.Storage;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Interfaces;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;
using Nito.AsyncEx;
using SQLite.Net.Attributes;

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

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private CacheStorageEntity<TModel> _cacheModel;
        private Task Initialize()
        {
            return ExecuteSafe(async () =>
            {
                using (await _asyncLock.LockAsync())
                {
                    if (_cacheModel != null)
                        return;

                    try
                    {
                        var json = await _cacheStorageService.GetCachedTextFileAsync(GetCacheFilePath());
                        var cacheModel = JsonConvert.DeserializeObject<CacheStorageEntity<TModel>>(json);
                        foreach (var model in cacheModel.Models)
                        {
                            _modelManager.Add(model);
                        }
                        _cacheModel = cacheModel;
                    }
                    catch
                    {
                        // exception ignored: no savegame or wrong savegame. does not matter either way
                        _cacheModel = new CacheStorageEntity<TModel>();
                    }
                }
            });
        }

        private ApiClient<TModel> _apiClient;

        private ApiClient<TModel> GetApiClient()
        {
            if (_apiClient != null)
                return _apiClient;

            _apiClient = new ApiClient<TModel>(_apiConfiguration.GetApiUri());
            return _apiClient;
        }


        public Task<bool> Sync()
        {
            return ExecuteSafe(async () =>
            {
                await Initialize();




                return true;
            });
        }

        private ModelInformation GetModelInfos(TModel model)
        {
            return _cacheModel.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
        }

        public Task<bool> Save(TModel model)
        {
            return ExecuteSafe(async () =>
            {
                if (model.GetId() == Guid.Empty)
                    model.SetId(new Guid());

                var objInfo = GetModelInfos(model);
                // CASE 1: Model is new
                if (objInfo == null)
                {
                    var collectionId = await _apiConfiguration.GetPrimaryGroupIdAsync(model.GetGroupIdentifier());

                    objInfo = new ModelInformation()
                    {
                        PendingAction = PendingAction.Create,
                        CollectionId = collectionId,
                        Id = model.GetId()
                    };
                    _cacheModel.ModelInformations.Add(objInfo);
                    _cacheModel.Models.Add(model);
                    _modelManager.Add(model);
                }
                else
                {
                    objInfo.PendingAction = PendingAction.Update;
                }

                if (objInfo.PendingAction == PendingAction.Create)
                {
                    var client = GetApiClient();
                    if (await client.CreateAsync(model, objInfo.CollectionId))
                    {
                        objInfo.PendingAction = PendingAction.None;
                    }
                }
                else if (objInfo.PendingAction == PendingAction.Update)
                {

                    var client = GetApiClient();
                    if (await client.UpdateAsync(model, objInfo.CollectionId))
                    {
                        objInfo.PendingAction = PendingAction.None;
                    }
                }
                return objInfo.PendingAction == PendingAction.None;
            });
        }

        public Task<bool> Remove(TModel model)
        {
            return ExecuteSafe(async () =>
            {

                return true;
            });
        }
    }
}
