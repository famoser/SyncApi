using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Enums;
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
    public class ApiRepository<TModel, TCollection> : PersistentCollectionRepository<TModel>, IApiRepository<TModel, TCollection>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
    {
        private readonly IApiCollectionRepository<TCollection> _collectionRepository;
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiStorageService _apiStorageService;
        private readonly IApiAuthenticationService _apiAuthenticationService;

        public ApiRepository(IApiCollectionRepository<TCollection> collectionRepository, IApiConfigurationService apiConfigurationService, IApiStorageService apiStorageService, IApiAuthenticationService apiAuthenticationService, IApiTraceService apiTraceService)
            : base(apiConfigurationService, apiStorageService, apiAuthenticationService, apiTraceService)
        {
            _collectionRepository = collectionRepository;
            _apiConfigurationService = apiConfigurationService;
            _apiStorageService = apiStorageService;
            _apiAuthenticationService = apiAuthenticationService;
        }

        private readonly AsyncLock _asyncLock = new AsyncLock();
        protected override async Task<bool> InitializeAsync()
        {
            using (await _asyncLock.LockAsync())
            {
                if (CollectionCache != null)
                    return true;

                CollectionCache = await _apiStorageService.GetCacheEntityAsync<CollectionCacheEntity<TModel>>(GetModelCacheFilePath());

                foreach (var collectionCacheModel in CollectionCache.Models)
                {
                    CollectionManager.Add(collectionCacheModel);
                }

                return true;
            }
        }

        public override Task<bool> SyncAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                var req = await _apiAuthenticationService.CreateRequestAsync<SyncEntityRequest, TCollection>();
                if (req == null)
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestCreationFailed);

                var client = GetApiClient();

                var synced = new List<int>();
                //first: push local data. This potentially will overwrite data from other devices, but with the VersionId we'll be able to revert back if things go wrong
                for (int index = 0; index < CollectionCache.ModelInformations.Count; index++)
                {
                    var index1 = index;
                    var mdl = ApiEntityHelper.CreateSyncEntity(CollectionCache.ModelInformations[index],
                        GetModelIdentifier(), () => CollectionCache.Models[index1]);
                    if (mdl != null)
                    {
                        req.SyncEntities.Add(mdl);
                        synced.Add(index);
                    }
                }
                var resp = await client.DoSyncRequestAsync(req);
                if (!resp.IsSuccessfull)
                    return new Tuple<bool, SyncActionError>(false, SyncActionError.RequestUnsuccessful);

                foreach (var modelInformation in synced)
                    CollectionCache.ModelInformations[modelInformation].PendingAction = PendingAction.None;

                foreach (var syncEntity in resp.SyncEntities)
                {
                    //new!
                    if (syncEntity.OnlineAction == OnlineAction.Create)
                    {
                        var mi = ApiEntityHelper.CreateCacheInformation<CacheInformations>(syncEntity);
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
                        model.SetId(syncEntity.Id);
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

                if (resp.SyncEntities.Any() || synced.Any())
                {
                    await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TModel>>();
                }

                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.SyncEntities, VerificationOption.CanAccessInternet | VerificationOption.IsAuthenticatedFully);
        }

        public Task<bool> SaveToCollectionAsync(TModel model, TCollection collection)
        {
            return ExecuteSafeAsync(async () =>
            {
                var info = CollectionCache.ModelInformations.FirstOrDefault(s => s.Id == model.GetId());
                if (info == null)
                {
                    info = await _apiAuthenticationService.CreateModelInformationAsync();

                    //get default collection if null
                    if (EqualityComparer<TCollection>.Default.Equals(collection, default(TCollection)))
                    {
                        collection = await _collectionRepository.GetDefaultCollection();
                    }

                    model.SetId(info.Id);
                    CollectionCache.ModelInformations.Add(info);
                    CollectionCache.Models.Add(model);
                    CollectionManager.Add(model);
                }
                else if (info.PendingAction == PendingAction.None
                         || info.PendingAction == PendingAction.Delete
                         || info.PendingAction == PendingAction.Read)
                {
                    info.PendingAction = PendingAction.Update;
                }
                if (!EqualityComparer<TCollection>.Default.Equals(collection, default(TCollection)))
                {
                    info.CollectionId = collection.GetId();
                }
                info.VersionId = Guid.NewGuid();

                await _apiStorageService.SaveCacheEntityAsync<CollectionCacheEntity<TModel>>();
                if (_apiConfigurationService.StartSyncAutomatically())
                    await SyncAsync();

                return new Tuple<bool, SyncActionError>(true, SyncActionError.None);
            }, SyncAction.SaveEntity, VerificationOption.None);
        }

        public Task<bool> SaveAsync(TModel model)
        {
            return SaveToCollectionAsync(model, default(TCollection));
        }

        public override Task<bool> RemoveAsync(TModel model)
        {
            return ExecuteSafeAsync(async () =>
            {
                var resp = await RemoveInternalAsync(model);
                return new Tuple<bool, SyncActionError>(true, resp);
            }, SyncAction.RemoveEntity, VerificationOption.None);
        }

        public override ObservableCollection<TModel> GetAllLazy()
        {
            return ExecuteSafeLazy(
            () => CollectionManager.GetObservableCollection(),
                async () =>
                {
                    if (_apiConfigurationService.StartSyncAutomatically())
                        await SyncAsync();
                    return SyncActionError.None;
                },
                SyncAction.GetEntities,
                VerificationOption.None
            );
        }

        public override Task<ObservableCollection<TModel>> GetAllAsync()
        {
            return ExecuteSafeAsync(
                async () => new Tuple<ObservableCollection<TModel>, SyncActionError>(
                    await GetAllInternalAsync(),
                    SyncActionError.None
                    ),
                SyncAction.GetEntitiesAsync,
                VerificationOption.None
                );
        }

        public override ObservableCollection<HistoryInformations<TModel>> GetHistoryLazy(TModel model)
        {
            return ExecuteSafeLazy(() => GetHistoryInternalLazy(model),
                async () =>
                {
                    if (_apiConfigurationService.StartSyncAutomatically())
                        await SyncHistoryAsync(model);
                    return SyncActionError.None;
                },
                SyncAction.GetEntityHistory,
                VerificationOption.None
            );
        }

        public override Task<ObservableCollection<HistoryInformations<TModel>>> GetHistoryAsync(TModel model)
        {
            return ExecuteSafeAsync(
                async () => new Tuple<ObservableCollection<HistoryInformations<TModel>>, SyncActionError>(
                    await GetHistoryInternalAsync(model),
                    SyncActionError.None
                    ),
                SyncAction.GetEntityHistory,
                VerificationOption.None
            );
        }

        public override Task<bool> SyncHistoryAsync(TModel model)
        {
            return ExecuteSafeAsync(
                async () => new Tuple<bool, SyncActionError>(
                    await SyncHistoryInternalAsync(model),
                    SyncActionError.None
                    ),
                SyncAction.SyncEntityHistory,
                VerificationOption.CanAccessInternet | VerificationOption.IsAuthenticatedFully
            );
        }
    }
}
