using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Interfaces;

namespace Famoser.SyncApi.Services
{
    public class ApiStorageService : IApiStorageService
    {
        private IStorageService _storageService;
        private IApiConfigurationService _apiConfigurationService;

        public ApiStorageService(IStorageService storageService)
        {
            _storageService = storageService;
        }

        public async Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            throw new NotImplementedException();
        }

        public async Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity)
        {
            throw new NotImplementedException();
        }

        public async Task<ApiCacheEntity> GetApiCacheEntityAsync()
        {
            throw new NotImplementedException();
        }

        public async Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity)
        {
            throw new NotImplementedException();
        }

        public Task<string> GetModelCacheJsonAsync(string identifier)
        {
            throw new NotImplementedException();
        }

        public Task<bool> SetModelCacheJsonAsync(string identifier, string json)
        {
            throw new NotImplementedException();
        }
    }
}
