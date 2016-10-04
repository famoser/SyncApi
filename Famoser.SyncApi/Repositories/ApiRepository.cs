using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.FrameworkEssentials.Services.Interfaces.Storage;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Interfaces;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel> : BaseHelper
    where TModel : ISyncModel
    {
        private readonly IModelManager<TModel> _modelManager;
        private readonly IStorageService _storageService;
        private IApiConfiguration _apiConfiguration;
        private IApiStorageService _apiStorageService;

        public ApiRepository(IModelManager<TModel> modelManager, IStorageService storageService, IApiConfiguration apiConfiguration, IApiStorageService apiStorageService)
        {
            _modelManager = modelManager;
            _storageService = storageService;
            _apiConfiguration = apiConfiguration;
            _apiStorageService = apiStorageService;
        }

        public ObservableCollection<TModel> GetAll()
        {
            Initialize();

            return _modelManager.GetObservableCollection();
        }


        private string GetModelCacheFilePath()
        {
            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            return _apiStorageService.GetFileName(model.GetUniqeIdentifier() + ".json", typeof(TModel));
        }

        private string GetApiCacheFilePath()
        {
            return _apiStorageService.GetFileName("api_cache.json");
        }

        private string GetApiRoamingFilePath()
        {
            return _apiStorageService.GetFileName("api_roaming.json");
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ModelCacheEntity<TModel> _apiCacheModel;
        private ApiCacheEntity _apiCacheEntity;
        private ApiRoamingEntity _apiRoamingEntity;
        private bool _isInitialized;
        private Task<bool> Initialize()
        {
            return ExecuteSafe(async () =>
            {
                using (await _asyncLock.LockAsync())
                {
                    if (_isInitialized)
                        return true;
                    
                    await InitializeFromStorageAsync();

                    var res = true;
                    if (_apiRoamingEntity == null)
                    {
                        res = await InitializeRoamingAsync();

                    }
                    if (_apiCacheEntity == null)
                    {
                        res = await InitializeDeviceAsync();
                    }
                    if (_apiCacheModel == null)
                    {
                        _apiCacheEntity = new ApiCacheEntity();
                    }

                    _isInitialized = res;
                    return res;
                }
            });
        }

        private Task<bool> SaveCacheAsync()
        {
            var json = JsonConvert.SerializeObject(_apiCacheModel);
            return _cacheStorageService.SetCachedTextFileAsync(GetModelCacheFilePath(), json);
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
                foreach (var modelInformation in _apiCacheModel.ModelInformations)
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
            return _apiCacheModel.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
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
                    _apiCacheModel.ModelInformations.Add(objInfo);
                    _apiCacheModel.Models.Add(model);
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
