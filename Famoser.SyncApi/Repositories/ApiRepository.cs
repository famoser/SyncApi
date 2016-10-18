using System;
using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Cache.Entitites;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel, TCollection, TDevice, TUser> : BaseHelper, IApiRepository<TModel, TCollection, TDevice, TUser>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        private readonly ICollectionManager<TModel> _collectionManager;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;

        public ApiRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
        {
            _collectionManager = new CollectionManager<TModel>();
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
        }

        public ObservableCollection<TModel> GetAll()
        {
            Initialize();

            return _collectionManager.GetObservableCollection();
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
                    
                    _isInitialized = await _apiStorageService.InitializeAsync();
                    
                    return _isInitialized;
                }
            });
        }


        public Task<bool> Sync()
        {
            return ExecuteSafe(async () =>
            {
                await Initialize();

                var request = new CollectionEntityRequest { OnlineAction = OnlineAction.Various };
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
                    _collectionManager.Add(model);
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
