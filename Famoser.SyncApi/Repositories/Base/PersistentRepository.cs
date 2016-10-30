using System;
using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Managers;
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
    public abstract class PersistentRepository<TModel> : BasePersistentRepository<TModel>, IPersistentRespository<TModel>
        where TModel : IUniqueSyncModel
    {
        protected readonly IManager<TModel> Manager;
        protected CacheEntity<TModel> CacheEntity;
        protected readonly ApiInformationEntity ApiInformationEntity;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;

        protected PersistentRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService)
            : base(apiConfigurationService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;

            Manager = _apiConfigurationService.GetManager<TModel>();
            ApiInformationEntity = apiConfigurationService.GetApiInformations();
        }

        protected ApiClient GetAuthApiClient()
        {
            return new ApiClient(ApiInformationEntity.Uri);
        }

        public Task<TModel> GetAsync()
        {
            return ExecuteSafe(async () =>
            {
                if (_apiConfigurationService.StartSyncAutomatically())
                    await SyncAsync();

                return Manager.GetModel();
            });
        }

        public Task<bool> SaveAsync()
        {
            return ExecuteSafe(async () =>
            {
                if (CacheEntity.ModelInformation.PendingAction == PendingAction.None
                    || CacheEntity.ModelInformation.PendingAction == PendingAction.Delete
                    || CacheEntity.ModelInformation.PendingAction == PendingAction.Read)
                {
                    CacheEntity.ModelInformation.VersionId = Guid.NewGuid();
                    CacheEntity.ModelInformation.PendingAction = PendingAction.Update;
                }
                await SaveCacheAsync();
                return true;
            });
        }

        public Task<bool> RemoveAsync()
        {
            return ExecuteSafe(async () =>
            {
                if (CacheEntity.ModelInformation.PendingAction != PendingAction.Create)
                {
                    CacheEntity.ModelInformation.PendingAction = PendingAction.Create;
                }
                await SaveCacheAsync();
                return true;
            });
        }

        protected async Task SaveCacheAsync()
        {
            try
            {
                await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TModel>>();
                if (_apiConfigurationService.CanUseWebConnection())
                    await SyncInternalAsync();
            }
            catch (Exception ex)
            {
                ExceptionLogger?.LogException(ex, this);
            }
        }
        private readonly AsyncLock _asyncLock = new AsyncLock();
        private ICollectionManager<HistoryInformations<TModel>> _historyManager;
        private CollectionCacheEntity<HistoryInformations<TModel>> _historyCache;
        private async Task<bool> InitializeHistoryAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (_historyCache != null)
                    return true;

                _historyManager = _apiConfigurationService.GetCollectionManager<HistoryInformations<TModel>>();
                _historyCache = await _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<HistoryInformations<TModel>>>(GetModelHistoryCacheFilePath(Manager.GetModel()));
                foreach (var historyInformationse in _historyCache.Models)
                {
                    _historyManager.Add(historyInformationse);
                }

                return true;
            }
        }

        protected abstract Task<bool> SyncHistoryInternalAsync();

        public Task<ObservableCollection<HistoryInformations<TModel>>> GetHistoryAsync()
        {
            return ExecuteSafe(async () =>
            {
                await InitializeHistoryAsync();
                if (_apiConfigurationService.StartSyncAutomatically())
                    await SyncHistoryAsync();

                return _historyManager.GetObservableCollection();
            });
        }

        public Task<bool> SyncHistoryAsync()
        {
            return ExecuteSafe(async () =>
            {
                await InitializeHistoryAsync();
                await SyncHistoryInternalAsync();

                return true;
            }, true);
        }

        public CacheInformations GetCacheInformations()
        {
            return CacheEntity.ModelInformation;
        }
    }
}
