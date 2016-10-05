using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;
using Famoser.SyncApi.Interfaces;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Services
{
    public class ApiStorageService : IApiStorageService
    {
        private readonly IStorageService _storageService;
        private readonly IApiConfigurationService _apiConfigurationService;

        public ApiStorageService(IStorageService storageService, IApiConfigurationService apiConfigurationService)
        {
            _storageService = storageService;
            _apiConfigurationService = apiConfigurationService;
        }

        public async Task<ApiRoamingEntity> GetApiRoamingEntityAsync()
        {
            var json = await _storageService.GetRoamingTextFileAsync(GetApiRoamingFilePath());
            return JsonConvert.DeserializeObject<ApiRoamingEntity>(json);
        }

        public Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity)
        {
            return _storageService.SetCachedTextFileAsync(GetApiRoamingFilePath(), JsonConvert.SerializeObject(entity));
        }

        public async Task<ApiCacheEntity> GetApiCacheEntityAsync()
        {
            var json = await _storageService.GetRoamingTextFileAsync(GetApiCacheFilePath());
            return JsonConvert.DeserializeObject<ApiCacheEntity>(json);
        }

        public Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity)
        {
            return _storageService.SetCachedTextFileAsync(GetApiCacheFilePath(), JsonConvert.SerializeObject(entity));
        }

        public Task<string> GetModelCacheJsonAsync(string identifier)
        {
            return _storageService.GetCachedTextFileAsync(identifier);
        }

        public Task<bool> SetModelCacheJsonAsync(string identifier, string json)
        {
            return _storageService.SetCachedTextFileAsync(identifier, json);
        }
        
        private string GetApiCacheFilePath()
        {
            return _apiConfigurationService.GetFileName("api_cache.json");
        }

        private string GetApiRoamingFilePath()
        {
            return _apiConfigurationService.GetFileName("api_roaming.json");
        }
    }
}
