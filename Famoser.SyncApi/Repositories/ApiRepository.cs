using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.FrameworkEssentials.Services.Interfaces.Storage;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Enums;
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

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ModelCacheEntity<TModel> _cacheModel;
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
                        //read out storage
                        //todo: test jsonconvert if null string is supplied

                        var json = await _cacheStorageService.GetCachedTextFileAsync(GetCacheFilePath());
                        var cacheModel = JsonConvert.DeserializeObject<ModelCacheEntity<TModel>>(json);
                        foreach (var model in cacheModel.Models)
                        {
                            _modelManager.Add(model);
                        }
                        _cacheModel = cacheModel;
                    }
                    catch
                    {
                        // exception ignored: no savegame or wrong savegame. does not matter either way
                        _cacheModel = new ModelCacheEntity<TModel>();
                    }
                }
            });
        }

        private Task<bool> SaveCacheAsync()
        {
            var json = JsonConvert.SerializeObject(_cacheModel);
            return _cacheStorageService.SetCachedTextFileAsync(GetCacheFilePath(), json);
        }

        private ApiClient<TModel> _apiClient;

        private async Task<ApiClient<TModel>> GetApiClient()
        {
            if (_apiClient != null)
                return _apiClient;

            _apiClient = new ApiClient<TModel>(_apiConfiguration.GetApiUri(), await _apiConfiguration.GetUserIdAsync());
            return _apiClient;
        }


        public Task<bool> Sync()
        {
            return ExecuteSafe(async () =>
            {
                await Initialize();

                var request = new RequestEntity { OnlineAction = OnlineAction.Various };
                foreach (var modelInformation in _cacheModel.ModelInformations)
                {
                    request.SyncEntities.Add(new SyncEntity()
                    {
                        VersionId = modelInformation.VersionId,
                        CollectionId = modelInformation.CollectionId,
                        Id = modelInformation.Id
                    });
                }

                var client = await GetApiClient();
                var resp = await client.DoRequestAsync(request);
                if (resp.RequestFailed)
                    return false;

                //all entities in here are updated
                foreach (var syncEntity in resp.SyncEntities)
                {

                }


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
                    objInfo.VersionId = Guid.NewGuid();
                    var client = await GetApiClient();
                    if (await client.CreateAsync(model, objInfo.CollectionId))
                    {
                        objInfo.PendingAction = PendingAction.None;
                    }
                }
                else if (objInfo.PendingAction == PendingAction.Update)
                {
                    objInfo.VersionId = Guid.NewGuid();
                    var client = await GetApiClient();
                    if (await client.UpdateAsync(model, objInfo.CollectionId))
                    {
                        objInfo.PendingAction = PendingAction.None;
                    }
                }
                await SaveCacheAsync();
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
