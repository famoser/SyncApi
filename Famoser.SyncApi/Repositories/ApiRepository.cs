using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiRepository<TModel, TCollection, TDevice, TUser> : PersistentCollectionRepository<TModel>, IApiRepository<TModel, TCollection, TDevice, TUser>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        private readonly ICollectionManager<TModel> _collectionManager;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        public ApiRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService)
            : base(apiAuthenticationService, apiStorageService, apiConfigurationService)
        {
            _collectionManager = new CollectionManager<TModel>();
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
            _apiAuthenticationService = apiAuthenticationService;
        }


        protected override async Task<bool> SyncInternalAsync()
        {
            if (!_apiAuthenticationService.IsAuthenticated())
            {
                if (!await _apiAuthenticationService.AuthenticateAsync())
                    return false;
            }

            var req = _apiAuthenticationService.CreateRequest<SyncEntityRequest>(OnlineAction.SyncVersion, typeof(TCollection));
            if (req == null)
                return false;

            var client = GetApiClient();

            var synced = new List<int>();
            //first: push local data. This potentially will overwrite data from other devices, but with the VersionId we'll be able to revert back at any time
            for (int index = 0; index < CollectionCache.ModelInformations.Count; index++)
            {
                //such elegance wooooow
                var index1 = index;
                var mdl = ApiEntityHelper.CreateSyncEntity(CollectionCache.ModelInformations[index], GetModelIdentifier(), () => CollectionCache.Models[index1]);
                if (mdl != null)
                {
                    req.SyncEntities.Add(mdl);
                    synced.Add(index);
                }
            }
            var resp = await client.DoSyncRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var modelInformation in synced)
                CollectionCache.ModelInformations[modelInformation].PendingAction = PendingAction.None;

            await _apiStorageService.SaveCollectionEntityAsync<TCollection>();

            req = _apiAuthenticationService.CreateRequest<SyncEntityRequest>(OnlineAction.SyncVersion, typeof(TCollection));
            //second request: get active version ids for all
            // this will return missing, updated & removed entities
            foreach (var collectionCacheModelInformation in CollectionCache.ModelInformations)
            {
                req.SyncEntities.Add(new SyncEntity()
                {
                    Id = collectionCacheModelInformation.Id,
                    VersionId = collectionCacheModelInformation.VersionId
                });
            }
            resp = await client.DoSyncRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var syncEntity in resp.SyncEntities)
            {
                //new!
                if (syncEntity.OnlineAction == OnlineAction.Create)
                {
                    var mi = ApiEntityHelper.CreateModelInformation(syncEntity);
                    var tcol = JsonConvert.DeserializeObject<TModel>(syncEntity.Content);
                    tcol.SetId(mi.Id);
                    CollectionCache.ModelInformations.Add(mi);
                    CollectionCache.Models.Add(tcol);
                    CollectionManager.Add(tcol);
                }
                //updated
                else if (syncEntity.OnlineAction == OnlineAction.Update)
                {
                    var index = CollectionCache.ModelInformations.FindIndex(d => d.Id == syncEntity.Id);
                    CollectionCache.ModelInformations[index].VersionId = syncEntity.VersionId;
                    var model = JsonConvert.DeserializeObject<TModel>(syncEntity.Content);
                    CollectionManager.Replace(CollectionCache.Models[index], model);
                    CollectionCache.Models[index] = model;
                }
                //removed
                else if (syncEntity.OnlineAction == OnlineAction.Delete)
                {
                    var index = CollectionCache.ModelInformations.FindIndex(d => d.Id == syncEntity.Id);
                    CollectionManager.Remove(CollectionCache.Models[index]);
                    CollectionCache.ModelInformations.RemoveAt(index);
                    CollectionCache.Models.RemoveAt(index);
                }
            }

            if (resp.SyncEntities.Any())
            {
                await _apiStorageService.SaveCollectionEntityAsync<TModel>();
            }

            return true;
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CollectionCache != null)
                    return true;

                CollectionCache = await _apiStorageService.GetCollectionCacheEntity<TModel>(GetModelCacheFilePath());
                
                foreach (var collectionCacheModel in CollectionCache.Models)
                {
                    CollectionManager.Add(collectionCacheModel);
                }

                return true;
            }
        }
    }
}
