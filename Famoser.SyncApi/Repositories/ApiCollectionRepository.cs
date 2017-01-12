using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories
{
    public class ApiCollectionRepository<TCollection> : PersistentCollectionRepository<TCollection>,
            IApiCollectionRepository<TCollection>
        where TCollection : class, ICollectionModel
    {
        private readonly IApiAuthenticationService _apiAuthenticationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiCollectionRepository(IApiAuthenticationService apiAuthenticationService,
            IApiStorageService apiStorageService, IApiConfigurationService apiConfigurationService, IApiTraceService traceService)
            : base(apiConfigurationService, apiStorageService, apiAuthenticationService, traceService)
        {
            _apiAuthenticationService = apiAuthenticationService;
            _apiStorageService = apiStorageService;
            _apiConfigurationService = apiConfigurationService;

            _apiAuthenticationService.RegisterCollectionRepository(this);
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();

        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CollectionCache != null)
                    return true;

                CollectionCache = await _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<TCollection>>(GetModelCacheFilePath());

                if (CollectionCache.ModelInformations.Count == 0)
                {
                    var model = await _apiConfigurationService.GetCollectionObjectAsync<TCollection>();
                    var mi = await _apiAuthenticationService.CreateModelInformationAsync();
                    model.SetId(mi.Id);
                    CollectionCache.Models.Add(model);
                    CollectionCache.ModelInformations.Add(mi);
                    await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
                }

                foreach (var collectionCacheModel in CollectionCache.Models)
                {
                    CollectionManager.Add(collectionCacheModel);
                }

                return true;
            }
        }

        protected override async Task<bool> SyncInternalAsync()
        {
            if (!await _apiAuthenticationService.IsAuthenticatedAsync())
            {
                return false;
            }

            var client = GetApiClient();

            var synced = new List<int>();
            var entities = new List<CollectionEntity>();
            //first: push local data. This potentially will overwrite data from other devices, but with the VersionId we'll be able to revert back at any time
            for (int index = 0; index < CollectionCache.ModelInformations.Count; index++)
            {
                //such elegance wooooow
                var index1 = index;
                var mdl = ApiEntityHelper.CreateCollectionEntity(CollectionCache.ModelInformations[index],
                    GetModelIdentifier(), () => CollectionCache.Models[index1]);
                if (mdl != null)
                {
                    entities.Add(mdl);
                    synced.Add(index);
                }
            }

            var req = await _apiAuthenticationService.CreateRequestAsync<CollectionEntityRequest>();
            if (req == null)
                return false;

            req.CollectionEntities = entities;
            var resp = await client.DoSyncRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var modelInformation in synced)
                CollectionCache.ModelInformations[modelInformation].PendingAction = PendingAction.None;
           
            foreach (var respCollectionEntity in resp.CollectionEntities)
            {
                //new
                if (respCollectionEntity.OnlineAction == OnlineAction.Create)
                {
                    var mi = ApiEntityHelper.CreateCacheInformation<CacheInformations>(respCollectionEntity);
                    var tcol = JsonConvert.DeserializeObject<TCollection>(respCollectionEntity.Content);
                    tcol.SetId(mi.Id);
                    CollectionCache.ModelInformations.Add(mi);
                    CollectionCache.Models.Add(tcol);
                    CollectionManager.Add(tcol);
                }
                //updated
                else if (respCollectionEntity.OnlineAction == OnlineAction.Update)
                {
                    var index = CollectionCache.ModelInformations.FindIndex(d => d.Id == respCollectionEntity.Id);
                    CollectionCache.ModelInformations[index].VersionId = respCollectionEntity.VersionId;
                    var model = JsonConvert.DeserializeObject<TCollection>(respCollectionEntity.Content);
                    model.SetId(respCollectionEntity.Id);
                    CollectionManager.Replace(CollectionCache.Models[index], model);
                    CollectionCache.Models[index] = model;
                }
                //removed
                else if (respCollectionEntity.OnlineAction == OnlineAction.Delete)
                {
                    var index = CollectionCache.ModelInformations.FindIndex(d => d.Id == respCollectionEntity.Id);
                    CollectionManager.Remove(CollectionCache.Models[index]);
                    CollectionCache.ModelInformations.RemoveAt(index);
                    CollectionCache.Models.RemoveAt(index);
                }
            }

            if (resp.CollectionEntities.Any() || synced.Any())
            {
                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
            }

            return true;
        }

        public Task<bool> AddUserToCollectionAsync(TCollection collection, IUserModel userModel)
        {
            return ExecuteSafeAsync(async () =>
            {
                if (!_apiConfigurationService.CanUseWebConnection())
                    return false;

                var req = await _apiAuthenticationService.CreateRequestAsync<AuthRequestEntity>();
                req.CollectionEntity = new CollectionEntity()
                {
                    Id = collection.GetId()
                };
                req.UserEntity = new UserEntity()
                {
                    Id = userModel.GetId()
                };
                var apiClient = GetApiClient();
                var resp = await apiClient.AuthenticateUserRequestAsync(req);
                return resp.IsSuccessfull;
            });
        }
    }
}
