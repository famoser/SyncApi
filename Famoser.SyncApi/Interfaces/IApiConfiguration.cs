using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiConfiguration
    {
        Uri GetApiUri();

        Task<ApiCacheEntity> GetApiCacheEntityAsync();
        Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity);

        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity);
    }
}
