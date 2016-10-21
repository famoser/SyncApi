using System;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;
using Famoser.SyncApi.Storage.Cache;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class PersistentCollectionRepository<TCollection> : BasePersistentRepository<TCollection>, IPersistentCollectionRespository<TCollection>
        where TCollection : IUniqueSyncModel
    {
        protected ICollectionManager<TCollection> CollectionManager = new CollectionManager<TCollection>();
        protected CollectionCacheEntity<TCollection> CollectionCache;

        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        protected PersistentCollectionRepository(IApiAuthenticationService apiAuthenticationService, IApiStorageService apiStorageService, IApiConfigurationService apiConfigurationService)
            : base(apiConfigurationService)
        {
            _apiAuthenticationService = apiAuthenticationService;
            _apiStorageService = apiStorageService;
            _apiConfigurationService = apiConfigurationService;
        }

        public ObservableCollection<TCollection> GetAllLazy()
        {
            SyncAsync();

            return CollectionManager.GetObservableCollection();
        }

        public Task<ObservableCollection<TCollection>> GetAll()
        {
            return ExecuteSafe(async () =>
            {
                await SyncInternalAsync();

                return CollectionManager.GetObservableCollection();
            });
        }
        
        public Task<bool> SaveAsync(TCollection model)
        {
            return ExecuteSafe(async () =>
            {
                var info = CollectionCache.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
                if (info == null)
                {
                    info = _apiAuthenticationService.CreateModelInformation();

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
                return await SyncInternalAsync();
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
                    return await _apiStorageService.SaveCacheEntityAsync<TCollection>();
                }
                if (info.PendingAction == PendingAction.None
                    || info.PendingAction == PendingAction.Update
                    || info.PendingAction == PendingAction.Read)
                {
                    info.PendingAction = PendingAction.Delete;
                }
                return await SyncInternalAsync();
            });
        }

        public Task<bool> RemoveAllAsync()
        {
            return ExecuteSafe(async () =>
            {
                foreach (var collectionCacheModelInformation in CollectionCache.ModelInformations)
                {
                    collectionCacheModelInformation.PendingAction = PendingAction.Delete;
                }
                return await SyncInternalAsync();
            });
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
