using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;
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
            IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService)
            : base(apiConfigurationService)
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
            return ExecuteSafe(async () =>
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
            return ExecuteSafe(async () =>
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
        
        public ObservableCollection<HistoryInformations<TCollection>> GetHistoryLazy(TCollection model)
        {
            throw new NotImplementedException();
        }

        public Task<ObservableCollection<HistoryInformations<TCollection>>> GetHistoryAsync(TCollection model)
        {
            throw new NotImplementedException();
        }


        //private readonly AsyncLock _asyncLock = new AsyncLock();
        //private ICollectionManager<HistoryInformations<TModel>> _historyManager;
        //private CollectionCacheEntity<HistoryInformations<TModel>> _historyCache;
        //private async Task<bool> InitializeHistoryAsync()
        //{
        //    using (await _asyncLock.LockAsync())
        //    {
        //        if (_historyCache != null)
        //            return true;

        //        _historyManager = _apiConfigurationService.GetCollectionManager<HistoryInformations<TModel>>();
        //        _historyCache = await _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TModel>>>(GetModelHistoryCacheFilePath(Manager.GetModel()));
        //        foreach (var historyInformationse in _historyCache.Models)
        //        {
        //            _historyManager.Add(historyInformationse);
        //        }

        //        return true;
        //    }
        //}

        //protected abstract Task<bool> SyncHistoryInternalAsync();

        //public Task<ObservableCollection<HistoryInformations<TModel>>> GetHistoryAsync()
        //{
        //    return ExecuteSafe(async () =>
        //    {
        //        await InitializeHistoryAsync();
        //        if (_apiConfigurationService.StartSyncAutomatically())
        //            await SyncHistoryAsync();

        //        return _historyManager.GetObservableCollection();
        //    });
        //}

        //public Task<bool> SyncHistoryAsync()
        //{
        //    return ExecuteSafe(async () =>
        //    {
        //        await InitializeHistoryAsync();
        //        await SyncHistoryInternalAsync();

        //        return true;
        //    }, true);
        //}

        public CacheInformations GetCacheInformations(TCollection model)
        {
            var index = CollectionCache.Models.IndexOf(model);
            return CollectionCache.ModelInformations[index];
        }

        public Task<bool> RemoveAllAsync()
        {
            return ExecuteSafe(async () =>
            {
                foreach (var collectionCacheModelInformation in CollectionCache.ModelInformations)
                {
                    collectionCacheModelInformation.PendingAction = PendingAction.Delete;
                }
                await SaveCacheAsync();
                return true;
            });
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
