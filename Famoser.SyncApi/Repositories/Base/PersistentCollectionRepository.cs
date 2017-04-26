using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Enums;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;
using Newtonsoft.Json;
using Nito.AsyncEx;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class PersistentCollectionRepository<TCollection> : BasePersistentRepository<TCollection>,
        IPersistentCollectionRespository<TCollection>
        where TCollection : IUniqueSyncModel
    {
        protected ICollectionManager<TCollection> CollectionManager;
        protected CollectionCacheEntity<TCollection> CollectionCache;

        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        protected PersistentCollectionRepository(IApiConfigurationService apiConfigurationService,
            IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService,
            IApiTraceService traceService)
            : base(apiConfigurationService, apiAuthenticationService, traceService)
        {
            _apiAuthenticationService = apiAuthenticationService;
            _apiStorageService = apiStorageService;
            _apiConfigurationService = apiConfigurationService;

            CollectionManager = _apiConfigurationService.GetCollectionManager<TCollection>();
        }

        protected async Task<ObservableCollection<TCollection>> GetAllInternalAsync()
        {
            if (_apiConfigurationService.StartSyncAutomatically())
            {
                await SyncAsync();
            }
            return CollectionManager.GetObservableCollection();
        }

        protected async Task<SyncActionError> RemoveInternalAsync(TCollection model)
        {
            var info = CollectionCache.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
            if (info == null)
            {
                return SyncActionError.EntityAlreadyRemoved;
            }

            CollectionManager.Remove(model);

            if (info.PendingAction == PendingAction.Create)
            {
                CollectionCache.ModelInformations.Remove(info);
                CollectionCache.Models.Remove(model);

                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
            }
            else if (info.PendingAction == PendingAction.None
                     || info.PendingAction == PendingAction.Update
                     || info.PendingAction == PendingAction.Read)
            {
                info.PendingAction = PendingAction.Delete;

                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
                if (_apiConfigurationService.CanUseWebConnection())
                    await SyncAsync();
            }

            return SyncActionError.None;
        }

        public abstract Task<bool> RemoveAsync(TCollection model);

        private readonly Dictionary<TCollection, ICollectionManager<HistoryInformations<TCollection>>>
            _historyCollectionManagers
                = new Dictionary<TCollection, ICollectionManager<HistoryInformations<TCollection>>>();

        private readonly Dictionary<TCollection, CollectionCacheEntity<HistoryInformations<TCollection>>>
            _historyCacheEntities
                = new Dictionary<TCollection, CollectionCacheEntity<HistoryInformations<TCollection>>>();

        private void EnsureExistanceOfHistoryManager(TCollection model)
        {
            if (!_historyCollectionManagers.ContainsKey(model))
            {
                HistoryCollectionManagers.Add(model,
                    _apiConfigurationService.GetCollectionManager<HistoryInformations<TCollection>>());
                HistoryCacheEntities.Add(model, null);
            }
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();

        private async Task InitializeHistoryAsync(TCollection model)
        {
            using (await _asyncLock.LockAsync())
            {
                if (_historyCacheEntities[model] == null)
                {
                    try
                    {
                        HistoryCacheEntities[model] = await _apiStorageService
                            .GetCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>(
                                GetModelHistoryCacheFilePath(model)
                            );
                        foreach (var historyInformationse in HistoryCacheEntities[model].Models)
                        {
                            CollectionCache.Models[i].SetId(_historyCacheEntities[model].ModelInformations[i].Id);
                            _historyCacheEntities[model].Models.Add(_historyCacheEntities[model].Models[i]);
                        }
                    }
                    catch //thrown if file not found
                    {
                        _historyCacheEntities[model] = new CollectionCacheEntity<HistoryInformations<TCollection>>();
                    }
                }
            }
        }

        protected ObservableCollection<HistoryInformations<TCollection>> GetHistoryInternalLazy(TCollection model)
        {
            EnsureExistanceOfHistoryManager(model);
            if (_apiConfigurationService.StartSyncAutomatically())
                SyncHistoryAsync(model);
            else
#pragma warning disable 4014
                InitializeHistoryAsync(model);
#pragma warning restore 4014

            return _historyCollectionManagers[model].GetObservableCollection();
        }

        public async Task<ObservableCollection<HistoryInformations<TCollection>>> GetHistoryInternalAsync(
            TCollection model)
        {
            EnsureExistanceOfHistoryManager(model);

            if (_apiConfigurationService.StartSyncAutomatically())
                await SyncHistoryAsync(model);
            else
                await InitializeHistoryAsync(model);

            return _historyCollectionManagers[model].GetObservableCollection();
        }

        protected async Task<bool> SyncHistoryInternalAsync(TCollection model)
        {
            await InitializeHistoryAsync(model);

            var client = GetApiClient();
            var cache = _historyCacheEntities[model];
            var manager = _historyCollectionManagers[model];

            var req = await _apiAuthenticationService.CreateRequestAsync<HistoryEntityRequest>(GetModelIdentifier());
            if (req == null)
                return false;

            req.Id = model.GetId();
            req.Identifier = model.GetClassIdentifier();
            // this will return missing entities
            foreach (var cacheModelInformation in cache.ModelInformations)
            {
                req.VersionIds.Add(cacheModelInformation.VersionId);
            }
            var resp = await client.DoEntityHistoryRequestAsync(req);
            if (!resp.IsSuccessfull)
                return false;

            foreach (var syncEntity in resp.CollectionEntities)
            {
                //new!
                if (syncEntity.OnlineAction == OnlineAction.Create)
                {
                    var tcol = JsonConvert.DeserializeObject<TCollection>(syncEntity.Content);
                    tcol.SetId(syncEntity.Id);
                    var mi = ApiEntityHelper.CreateHistoryInformation<TCollection>(syncEntity);
                    mi.Model = tcol;
                    tcol.SetId(mi.Id);
                    cache.ModelInformations.Add(mi);
                    cache.Models.Add(mi);
                    manager.Add(mi);
                }
            }

            if (resp.CollectionEntities.Any())
                return await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>();

            return true;
        }


        protected async Task<bool> RemoveHistoryInternalAsync(IEnumerable<TCollection> models)
        {
            foreach (var model in models)
            {
                HistoryCacheEntities.Remove(model);
                HistoryCollectionManagers.Remove(model);
            }

            return await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>();
        }

        public CacheInformations GetCacheInformations(TCollection model)
        {
            var index = CollectionCache.Models.IndexOf(model);
            return CollectionCache.ModelInformations[index];
        }

        public void SetCollectionManager(ICollectionManager<TCollection> manager)
        {
            manager.TransferFrom(CollectionManager);
            CollectionManager = manager;
        }

        public ICollectionManager<TCollection> GetCollectionManager()
        {
            return CollectionManager;
        }

        public abstract ObservableCollection<TCollection> GetAllLazy();

        public abstract Task<ObservableCollection<TCollection>> GetAllAsync();

        public abstract ObservableCollection<HistoryInformations<TCollection>> GetHistoryLazy(TCollection model);

        public abstract Task<ObservableCollection<HistoryInformations<TCollection>>> GetHistoryAsync(TCollection model);

        public abstract Task<bool> SyncHistoryAsync(TCollection model);

        public override async Task<bool> CleanUpAsync()
        {
            var res =
                await _apiStorageService.EraseCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>();
            res &= await _apiStorageService.EraseCacheEntityAsync<CollectionCacheEntity<TCollection>>();
            return res;
        }
    }
}