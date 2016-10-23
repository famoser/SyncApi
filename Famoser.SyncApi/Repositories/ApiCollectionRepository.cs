using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Base;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
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
            IApiStorageService apiStorageService, IApiConfigurationService apiConfigurationService)
            : base(apiAuthenticationService, apiStorageService, apiConfigurationService)
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

                CollectionCache =
                    await _apiStorageService.GetCollectionCacheEntity<TCollection>(GetModelCacheFilePath());

                if (CollectionCache.ModelInformations.Count == 0)
                {
                    var mi = _apiAuthenticationService.CreateModelInformation();
                    var model = await _apiConfigurationService.GetCollectionObjectAsync<TCollection>();
                    mi.Id = Guid.NewGuid();
                    model.SetId(mi.Id);
                    CollectionCache.Models.Add(model);
                    CollectionCache.ModelInformations.Add(mi);
                    await _apiStorageService.SaveCollectionEntityAsync<TCollection>();
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
            if (!_apiAuthenticationService.IsAuthenticated())
            {
                if (!await _apiAuthenticationService.AuthenticateAsync())
                    return false;
            }

            var req = _apiAuthenticationService.CreateRequestAsync<CollectionEntityRequest>(OnlineAction.SyncEntity);
            if (req == null)
                return false;

            var client = GetApiClient();

            var synced = new List<int>();
            //first: push local data. This potentially will overwrite data from other devices, but with the VersionId we'll be able to revert back at any time
            for (int index = 0; index < CollectionCache.ModelInformations.Count; index++)
            {
                //such elegance wooooow
                var index1 = index;
                var mdl = ApiEntityHelper.CreateCollectionEntity(CollectionCache.ModelInformations[index],
                    GetModelIdentifier(), () => CollectionCache.Models[index1]);
                if (mdl != null)
                {
                    req.CollectionEntities.Add(mdl);
                    synced.Add(index);
                }
            }
            var resp = await client.DoSyncRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var modelInformation in synced)
                CollectionCache.ModelInformations[modelInformation].PendingAction = PendingAction.None;

            await _apiStorageService.SaveCollectionEntityAsync<TCollection>();

            req = _apiAuthenticationService.CreateRequestAsync<CollectionEntityRequest>(OnlineAction.SyncVersion);
            //second request: get active version ids for all
            // this will return missing, updated & removed entities
            foreach (var collectionCacheModelInformation in CollectionCache.ModelInformations)
            {
                req.CollectionEntities.Add(new CollectionEntity()
                {
                    Id = collectionCacheModelInformation.Id,
                    VersionId = collectionCacheModelInformation.VersionId
                });
            }
            resp = await client.DoSyncRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var respCollectionEntity in resp.CollectionEntities)
            {
                //new!
                if (respCollectionEntity.OnlineAction == OnlineAction.Create)
                {
                    var mi = ApiEntityHelper.CreateModelInformation(respCollectionEntity);
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

            if (resp.CollectionEntities.Any())
            {
                await _apiStorageService.SaveCollectionEntityAsync<TCollection>();
            }

            return true;
        }

        public Task<bool> AddUserToCollectionAsync(TCollection collection, Guid userId)
        {
            throw new NotImplementedException();
        }
    }
}
