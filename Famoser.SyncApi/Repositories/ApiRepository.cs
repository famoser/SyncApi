using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Api;
using Famoser.SyncApi.Entities.Storage;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Cache.Entitites;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Interfaces;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Services;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel> : BaseHelper
    where TModel : ISyncModel
    {
        private readonly IModelManager<TModel> _modelManager;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;

        public ApiRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
        {
            _modelManager = new ModelManager<TModel>();
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
        }

        public ObservableCollection<TModel> GetAll()
        {
            Initialize();

            return _modelManager.GetObservableCollection();
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private bool _isInitialized;
        private Task<bool> Initialize()
        {
            return ExecuteSafe(async () =>
            {
                using (await _asyncLock.LockAsync())
                {
                    if (_isInitialized)
                        return true;

                    var res = true;

                    var apiRoamingEntity = await _apiStorageService.GetApiRoamingEntityAsync();
                    if (apiRoamingEntity == null)
                    {
                        var helper = GetApiAuthorizationHelper();
                        var apiRoaming = new ApiRoamingEntity();
                        var apiCache = new ApiCacheEntity();
                        var cache = new ModelCacheEntity<TModel>();
                        var userId = Guid.NewGuid();
                        var deviceId = Guid.NewGuid();
                        if (await helper.InitializeUserAsync(userId, deviceId, apiRoaming, apiCache, cache))
                        {
                            await _apiStorageService.SetApiRoamingEntityAsync(apiRoaming);
                            await _apiStorageService.SetApiCacheEntityAsync(apiCache);
                            await _apiStorageService.SetModelCacheJsonAsync(GetModelCacheFilePath(), cache);
                        }
                        else
                            res = false;
                    }
                    else
                    {
                        var apiCacheEntity = await _apiStorageService.GetApiCacheEntityAsync();
                        if (apiCacheEntity == null)
                        {
                            var helper = GetApiAuthorizationHelper();
                            var apiCache = new ApiCacheEntity();
                            var cache = new ModelCacheEntity<TModel>();
                            var deviceId = Guid.NewGuid();
                            if (await helper.InitializeDeviceAsync(deviceId, apiRoamingEntity, apiCache, cache))
                            {
                                await _apiStorageService.SetApiCacheEntityAsync(apiCache);
                                await _apiStorageService.SetModelCacheJsonAsync(GetModelCacheFilePath(), cache);
                            }
                            else
                                res = false;
                        }
                        else
                        {
                            //read out storage
                            var apiCacheModel = await _apiStorageService.GetModelCacheAsync<TModel>(GetModelCacheFilePath());
                            if (apiCacheModel == null)
                            {
                                await _apiStorageService.SetModelCacheJsonAsync(GetModelCacheFilePath(), new ModelCacheEntity<TModel>());
                            }
                            else
                            {
                                foreach (var model in apiCacheModel.Models)
                                {
                                    _modelManager.Add(model);
                                }
                            }
                        }
                    }

                    _isInitialized = res;
                    return res;
                }
            });
        }


        public Task<bool> Sync()
        {
            return ExecuteSafe(async () =>
            {
                await Initialize();

                var request = new RequestEntity { OnlineAction = OnlineAction.Various };
                foreach (var modelInformation in _apiStorageService.GetModelCache<TModel>(GetModelCacheFilePath()).ModelInformations)
                {
                    request.SyncEntities.Add(new SyncEntity()
                    {
                        VersionId = modelInformation.VersionId,
                        CollectionId = modelInformation.CollectionId,
                        Id = modelInformation.Id
                    });
                }

                var client = GetApiClient();
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
            return GetModelCache().ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
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
                    var collectionInfo = GetApiCache().GetSaveCollection(model.GetGroupIdentifier());
                    if (collectionInfo == null)
                    {
                        var helper = GetApiAuthorizationHelper();
                        if (!await helper.InitializeCollectionAsync(Guid.NewGuid(), GetApiCache(), GetModelCache()))
                            return false;
                    }

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
