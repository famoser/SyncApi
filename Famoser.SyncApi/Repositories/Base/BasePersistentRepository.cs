using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class BasePersistentRepository<TModel> : IBasePersistentRepository
        where TModel : IUniqueSyncModel
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiAuthenticationService _apiAuthenticationService;
        private readonly IApiTraceService _apiTraceService;
        protected BasePersistentRepository(IApiConfigurationService apiConfigurationService, IApiAuthenticationService apiAuthenticationService, IApiTraceService traceService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiAuthenticationService = apiAuthenticationService;
            _apiTraceService = traceService;
        }

        protected abstract Task<bool> InitializeAsync();

        protected Task<T> ExecuteSafeAsync<T>(Func<Task<Tuple<T, SyncActionError>>> func, SyncAction action, VerificationOption verification)
        {
            return ExecuteSafeInternalAsync(() => default(T),
                async ev =>
                {
                    var res = await func();
                    ev.SetSyncActionResult(res.Item2);
                    return res.Item1;
                },
                action,
                verification
            );
        }

        protected T ExecuteSafeLazy<T>(Func<T> returnExecute, Func<Task<SyncActionError>> func, SyncAction action, VerificationOption verification)
        {
            return ExecuteSafeInternalLazy(returnExecute,
                async ev =>
                {
                    var res = await func();
                    ev.SetSyncActionResult(res);
                },
                action,
                verification
            );
        }

        protected Task<T> ExecuteSafeAsync<T>(Func<Tuple<T, SyncActionError>> func, SyncAction action, VerificationOption verification)
        {
            return ExecuteSafeInternalAsync(() => default(T),
#pragma warning disable CS1998 // Async method lacks 'await' operators and will run synchronously
                //to not have to duplicate code further I will not refactor to also accept a non Task func
                async ev =>
                {
                    var res = func();
                    ev.SetSyncActionResult(res.Item2);
                    return res.Item1;
                },
#pragma warning restore CS1998 // Async method lacks 'await' operators and will run synchronously
                action,
                verification
            );
        }

        protected Task ExecuteSafeAsync(Func<Task<SyncActionError>> func, SyncAction action, VerificationOption verification)
        {
            return ExecuteSafeInternalAsync(() => false,
                async ev =>
                {
                    var res = await func();
                    ev.SetSyncActionResult(res);
                    return true;
                },
                action,
                verification
            );
        }

        private async Task<T> ExecuteSafeInternalAsync<T>(Func<T> defaultReturn, Func<ISyncActionInformation, Task<T>> executeReturn, SyncAction action, VerificationOption verification)
        {
            var ev = _apiTraceService.CreateSyncActionInformation(action);

            try
            {
                //very similar logic in ExecuteSafeInternalLazy
                if (!await InitializeAsync())
                {
                    ev.SetSyncActionResult(SyncActionError.InitializationFailed);
                    return defaultReturn();
                }

                if (verification.HasFlag(VerificationOption.CanAccessInternet) && !_apiConfigurationService.CanUseWebConnection())
                {
                    ev.SetSyncActionResult(SyncActionError.WebAccessDenied);
                }
                else if (verification.HasFlag(VerificationOption.CanAccessInternet) && await _apiAuthenticationService.IsAuthenticatedAsync())
                {
                    ev.SetSyncActionResult(SyncActionError.NotAuthenticatedFully);
                }
                else
                {
                    return await executeReturn(ev);
                }
            }
            catch (Exception ex)
            {
                ev.SetSyncActionException(ex);
            }
            return defaultReturn();
        }

        private T ExecuteSafeInternalLazy<T>(Func<T> returnAction, Func<ISyncActionInformation, Task> executeAction, SyncAction action, VerificationOption verification)
        {
            var ev = _apiTraceService.CreateSyncActionInformation(action);

            try
            {
                InitializeAsync().ContinueWith(async e =>
                {
                    //very similar logic in ExecuteSafeInternalAsync
                    if (!e.Result)
                    {
                        ev.SetSyncActionResult(SyncActionError.InitializationFailed);
                    }
                    else
                    {
                        if (verification.HasFlag(VerificationOption.CanAccessInternet) && !_apiConfigurationService.CanUseWebConnection())
                        {
                            ev.SetSyncActionResult(SyncActionError.WebAccessDenied);
                        }
                        else if (verification.HasFlag(VerificationOption.CanAccessInternet) &&  await _apiAuthenticationService.IsAuthenticatedAsync())
                        {
                            ev.SetSyncActionResult(SyncActionError.NotAuthenticatedFully);
                        }
                        else
                        {
                            await executeAction(ev);
                        }
                    }
                });
            }
            catch (Exception ex)
            {
                ev.SetSyncActionException(ex);
            }
            return returnAction();
        }

        protected string GetModelHistoryCacheFilePath(TModel model)
        {
            return _apiConfigurationService.GetFileName(model.GetId() + "_history.json", typeof(TModel));
        }

        private string _modelCacheFilePath;
        protected string GetModelCacheFilePath()
        {
            if (_modelCacheFilePath != null)
                return _modelCacheFilePath;

            _modelCacheFilePath = _apiConfigurationService.GetFileName(GetModelIdentifier() + ".json", typeof(TModel));

            return _modelCacheFilePath;
        }

        private string _modelIdentifier;
        protected string GetModelIdentifier()
        {
            if (_modelIdentifier != null)
                return _modelIdentifier;

            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            _modelIdentifier = model.GetClassIdentifier();

            return _modelIdentifier;
        }

        private ApiClient _apiClient;
        protected ApiClient GetApiClient()
        {
            if (_apiClient != null)
                return _apiClient;

            _apiClient = new ApiClient(_apiConfigurationService.GetApiInformations().Uri, _apiTraceService);
            return _apiClient;
        }

        private bool _isDisposed;
        protected virtual void Dispose(bool disposing)
        {
            if (_isDisposed)
                if (disposing)
                    _apiClient.Dispose();
            _isDisposed = true;
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }

        public abstract Task<bool> SyncAsync();
    }
}
