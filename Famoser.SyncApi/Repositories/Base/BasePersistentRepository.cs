using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Api;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class BasePersistentRepository<TModel> : IBasePersistentRepository, IDisposable
        where TModel : IUniqueSyncModel
    {
        private readonly IApiConfigurationService _apiConfigurationService;
        protected BasePersistentRepository(IApiConfigurationService apiConfigurationService)
        {
            _apiConfigurationService = apiConfigurationService;
        }

        public Task<bool> SyncAsync()
        {
            return ExecuteSafe(async () => await SyncInternalAsync());
        }

        public void SetExceptionLogger(IExceptionLogger exceptionLogger)
        {
            _exceptionLogger = exceptionLogger;
        }

        protected abstract Task<bool> SyncInternalAsync();
        protected abstract Task<bool> InitializeAsync();


        private IExceptionLogger _exceptionLogger;
        protected async Task<T> ExecuteSafe<T>(Func<Task<T>> func)
        {
            try
            {
                if (!await InitializeAsync())
                    return default(T);

                return await func();
            }
            catch (Exception ex)
            {
                _exceptionLogger?.LogException(ex, this);
            }
            return default(T);
        }

        private string _modelCacheFilePath;
        protected string GetModelCacheFilePath()
        {
            if (_modelCacheFilePath == null)
                return _modelCacheFilePath;

            _modelCacheFilePath = _apiConfigurationService.GetFileName(GetModelIdentifier() + ".json", typeof(TModel));

            return _modelCacheFilePath;
        }

        private string _modelIdentifier;
        protected string GetModelIdentifier()
        {
            if (_modelIdentifier == null)
                return _modelIdentifier;

            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            _modelIdentifier = model.GetUniqeIdentifier();

            return _modelIdentifier;
        }

        private ApiClient _apiClient;
        protected ApiClient GetApiClient()
        {
            if (_apiClient != null)
                return _apiClient;

            _apiClient = new ApiClient(_apiConfigurationService.GetApiInformations().Uri);
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
