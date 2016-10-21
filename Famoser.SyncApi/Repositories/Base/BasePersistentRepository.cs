﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Models.Interfaces.Base;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories.Base
{
    public abstract class BasePersistentRepository<TModel> : IBasePersistentRepository
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

            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            _modelCacheFilePath = _apiConfigurationService.GetFileName(model.GetUniqeIdentifier() + ".json", typeof(TModel));

            return _modelCacheFilePath;
        }
    }
}
