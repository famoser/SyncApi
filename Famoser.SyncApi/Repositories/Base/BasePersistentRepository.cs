using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class BasePersistentRepository<TModel> : IBasePersistentRepository
        where TModel : IUniqueSyncModel
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        private readonly IApiTraceService _apiTraceService;
        protected BasePersistentRepository(IApiConfigurationService apiConfigurationService, IApiTraceService traceService)
        {
            _apiConfigurationService = apiConfigurationService;
            _apiTraceService = traceService;
        }

        public Task<bool> SyncAsync()
        {
            return ExecuteSafeAsync(async () =>
            {
                if (_apiConfigurationService.CanUseWebConnection())
                    return await SyncInternalAsync();
                return false;
            });
        }

        public void SetExceptionLogger(IExceptionLogger exceptionLogger)
        {
            ExceptionLogger = exceptionLogger;
        }

        protected abstract Task<bool> SyncInternalAsync();
        protected abstract Task<bool> InitializeAsync();

        protected IExceptionLogger ExceptionLogger;
        protected async Task<T> ExecuteSafeAsync<T>(Func<Task<T>> func, bool ensureWebCanBeUsed = false)
        {
            try
            {
                if (!await InitializeAsync())
                    return default(T);

                if (!ensureWebCanBeUsed || _apiConfigurationService.CanUseWebConnection())
                    return await func();
            }
            catch (Exception ex)
            {
                ExceptionLogger?.LogException(ex, this);
            }
            return default(T);
        }

        protected async Task ExecuteSafeAsync(Func<Task> func, bool ensureWebCanBeUsed = false)
        {
            try
            {
                if (!await InitializeAsync())
                    return;

                if (!ensureWebCanBeUsed || _apiConfigurationService.CanUseWebConnection())
                    await func();
            }
            catch (Exception ex)
            {
                ExceptionLogger?.LogException(ex, this);
            }
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
    }
}
