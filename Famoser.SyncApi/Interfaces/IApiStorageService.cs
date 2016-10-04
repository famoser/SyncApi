using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiStorageService
    {
        Task<ApiCacheEntity> GetApiCacheEntityAsync();
        Task<bool> SetApiCacheEntityAsync(ApiCacheEntity entity);

        Task<ApiRoamingEntity> GetApiRoamingEntityAsync();
        Task<bool> SetApiRoamingEntityAsync(ApiRoamingEntity entity);

        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
