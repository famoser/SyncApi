using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
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
            IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService, IApiTraceService traceService)
            : base(apiConfigurationService, traceService)
        {
            _apiAuthenticationService = apiAuthenticationService;
            _apiStorageService = apiStorageService;
            _apiConfigurationService = apiConfigurationService;

            CollectionManager = _apiConfigurationService.GetCollectionManager<TCollection>();
        }

        public ObservableCollection<TCollection> GetAllLazy()
        {
            if (_apiConfigurationService.StartSyncAutomatically())
                SyncAsync();
            else
                InitializeAsync();

            return CollectionManager.GetObservableCollection();
        }

        public async Task<ObservableCollection<TCollection>> GetAllAsync()
        {
            if (_apiConfigurationService.StartSyncAutomatically())
                await SyncAsync();
            else
            {
                try
                {
                    await InitializeAsync();
                }
                catch (Exception ex)
                {
                    ExceptionLogger?.LogException(ex, this);
                }
            }
            return CollectionManager.GetObservableCollection();
        }

        public Task<bool> SaveAsync(TCollection model)
        {
            return ExecuteSafeAsync(async () =>
            {
                var info = CollectionCache.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
                if (info == null)
                {
                    info = await _apiAuthenticationService.CreateModelInformationAsync();

                    model.SetId(info.Id);
                    CollectionCache.ModelInformations.Add(info);
                    CollectionCache.Models.Add(model);
                    CollectionManager.Add(model);
                }
                else if (info.PendingAction == PendingAction.None
                         || info.PendingAction == PendingAction.Delete
                         || info.PendingAction == PendingAction.Read)
                {
                    info.VersionId = Guid.NewGuid();
                    info.PendingAction = PendingAction.Update;
                }
                await SaveCacheAsync();
                return true;
            });
        }

        public Task<bool> RemoveAsync(TCollection model)
        {
            return ExecuteSafeAsync(async () =>
            {
                var info = CollectionCache.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
                if (info == null)
                {
                    return true;
                }
                if (info.PendingAction == PendingAction.Create)
                {
                    CollectionManager.Remove(model);
                    CollectionCache.ModelInformations.Remove(info);
                    CollectionCache.Models.Remove(model);
                    return await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
                }
                if (info.PendingAction == PendingAction.None
                    || info.PendingAction == PendingAction.Update
                    || info.PendingAction == PendingAction.Read)
                {
                    info.PendingAction = PendingAction.Delete;
                }
                await SaveCacheAsync();
                return true;
            });
        }
        
        protected readonly Dictionary<TCollection, ICollectionManager<HistoryInformations<TCollection>>>
            HistoryCollectionManagers
                = new Dictionary<TCollection, ICollectionManager<HistoryInformations<TCollection>>>();

        protected readonly Dictionary<TCollection, CollectionCacheEntity<HistoryInformations<TCollection>>>
            HistoryCacheEntities
                = new Dictionary<TCollection, CollectionCacheEntity<HistoryInformations<TCollection>>>();

        private void EnsureExistanceOfHistoryManager(TCollection model)
        {
            if (!HistoryCollectionManagers.ContainsKey(model))
            {
                HistoryCollectionManagers.Add(model,
                    _apiConfigurationService.GetCollectionManager<HistoryInformations<TCollection>>());
                HistoryCacheEntities.Add(model, null);
            }
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        private async Task<bool> InitializeHistoryAsync(TCollection model)
        {
            using (await _asyncLock.LockAsync())
            {
                if (HistoryCacheEntities[model] != null)
                    return true;

                HistoryCacheEntities[model] =
                    await
                        _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>(
                            GetModelHistoryCacheFilePath(model));
                foreach (var historyInformationse in HistoryCacheEntities[model].Models)
                {
                    HistoryCollectionManagers[model].Add(historyInformationse);
                }

                return true;
            }
        }

        public ObservableCollection<HistoryInformations<TCollection>> GetHistoryLazy(TCollection model)
        {
            EnsureExistanceOfHistoryManager(model);
            if (_apiConfigurationService.StartSyncAutomatically())
                SyncHistoryAsync(model);
            else
#pragma warning disable 4014 //disabled as we do not want to wait here explicitly
                InitializeHistoryAsync(model);
#pragma warning restore 4014

            return HistoryCollectionManagers[model].GetObservableCollection();
        }

        public Task<ObservableCollection<HistoryInformations<TCollection>>> GetHistoryAsync(TCollection model)
        {
            return ExecuteSafeAsync(async () =>
            {
                EnsureExistanceOfHistoryManager(model);

                if (_apiConfigurationService.StartSyncAutomatically())
                    await SyncHistoryAsync(model);
                else
                    try
                    {
                        await InitializeHistoryAsync(model);
                    }
                    catch (Exception ex)
                    {
                        ExceptionLogger?.LogException(ex);
                    }

                return HistoryCollectionManagers[model].GetObservableCollection();
            });
        }

        protected async Task<bool> SyncHistoryInternalAsync(TCollection model)
        {
            if (!await _apiAuthenticationService.IsAuthenticatedAsync())
                return false;

            var client = GetApiClient();
            var cache = HistoryCacheEntities[model];
            var manager = HistoryCollectionManagers[model];

            var req = await _apiAuthenticationService.CreateRequestAsync<HistoryEntityRequest>();
            if (req == null)
                return false;

            req.Id = model.GetId();
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
                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TCollection>>>();

            return true;
        }

        public Task<bool> SyncHistoryAsync(TCollection model)
        {
            return ExecuteSafeAsync(async () =>
            {
                await InitializeHistoryAsync(model);
                if (_apiConfigurationService.CanUseWebConnection())
                    return await SyncHistoryInternalAsync(model);
                return false;
            });
        }

        public CacheInformations GetCacheInformations(TCollection model)
        {
            var index = CollectionCache.Models.IndexOf(model);
            return CollectionCache.ModelInformations[index];
        }

        protected async Task SaveCacheAsync()
        {
            try
            {
                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TCollection>>();
                if (_apiConfigurationService.CanUseWebConnection())
                    await SyncInternalAsync();
            }
            catch (Exception ex)
            {
                ExceptionLogger?.LogException(ex, this);
            }
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
    }
}
