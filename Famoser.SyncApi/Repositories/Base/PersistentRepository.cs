using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Clients;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Storage.Cache;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class PersistentRepository<TModel> : BaseHelper, IPersistentRespository<TModel>
    {
        protected readonly IManager<TModel> Manager = new Manager<TModel>();
        protected CacheEntity<TModel> CacheEntity;
        protected readonly ApiInformationEntity ApiInformationEntity;

        protected PersistentRepository(IApiConfigurationService apiConfigurationService)
        {
            ApiInformationEntity = apiConfigurationService.GetApiInformations();
        }

        protected AuthApiClient GetAuthApiClient()
        {
            return new AuthApiClient(ApiInformationEntity.Uri);
        }

        public abstract Task<bool> SyncAsync();
        protected abstract Task<bool> InitializeAsync();
        public async Task<TModel> GetAsync()
        {
            if (!await InitializeAsync())
                return default(TModel);

            await SyncAsync();

            return Manager.GetModel();
        }


        public Task<bool> SaveAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction == PendingAction.None
                || CacheEntity.ModelInformation.PendingAction == PendingAction.Delete
                || CacheEntity.ModelInformation.PendingAction == PendingAction.Read)
            {
                CacheEntity.ModelInformation.VersionId = Guid.NewGuid();
                CacheEntity.ModelInformation.PendingAction = PendingAction.Update;
                return SyncAsync();
            }
            return SyncAsync();
        }

        public Task<bool> RemoveAsync()
        {
            if (CacheEntity.ModelInformation.PendingAction != PendingAction.Create)
            {
                CacheEntity.ModelInformation.PendingAction = PendingAction.Create;
            }
            return SyncAsync();
        }
    }
}
