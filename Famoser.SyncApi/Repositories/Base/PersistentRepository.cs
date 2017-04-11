using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class PersistentRepository<TModel> : BasePersistentRepository<TModel>, IPersistentRespository<TModel>
        where TModel : IUniqueSyncModel
    {
        protected readonly IManager<TModel> Manager;
        protected CacheEntity<TModel> CacheEntity;
        protected readonly ApiInformation ApiInformation;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiTraceService _apiTraceService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        protected PersistentRepository(IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiTraceService traceService, IApiAuthenticationService apiAuthenticationService = null)
            : base(apiConfigurationService, apiAuthenticationService, traceService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
            _apiTraceService = traceService;
            _apiAuthenticationService = apiAuthenticationService;

            Manager = _apiConfigurationService.GetManager<TModel>();
            ApiInformation = apiConfigurationService.GetApiInformations();
        }

        protected ApiClient GetAuthApiClient()
        {
            return new ApiClient(ApiInformation.Uri, _apiTraceService);
        }

        public async Task<TModel> GetInternalAsync()
        {
            if (_apiConfigurationService.StartSyncAutomatically())
                await SyncAsync();

            return Manager.GetModel();
        }

        public async Task<bool> SaveInternalAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction == PendingAction.None
                || CacheEntity.ModelInformation.PendingAction == PendingAction.Delete
                || CacheEntity.ModelInformation.PendingAction == PendingAction.Read)
            {
                CacheEntity.ModelInformation.VersionId = Guid.NewGuid();
                CacheEntity.ModelInformation.PendingAction = PendingAction.Update;
            }

            await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TModel>>();
            if (_apiConfigurationService.CanUseWebConnection())
                await SyncAsync();

            return true;
        }

        public async Task<bool> RemoveInternalAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction == PendingAction.Create)
            {
                CacheEntity.ModelInformation.PendingAction = PendingAction.DeleteLocally;
            }
            else
            {
                CacheEntity.ModelInformation.PendingAction = PendingAction.Delete;
            }

            await _apiStorageService.SaveCacheEntityAsync<CacheEntity<TModel>>();
            if (_apiConfigurationService.CanUseWebConnection())
                await SyncAsync();

            return true;
        }

        public CacheInformations GetCacheInformations()
        {
            return CacheEntity.ModelInformation;
        }

        public abstract Task<TModel> GetAsync();

        public abstract Task<bool> SaveAsync();

        public abstract Task<bool> RemoveAsync();
    }
}
