using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiCollectionAuthenticationService
    {
        /// <summary>
        /// Get Api informations
        /// </summary>
        /// <returns></returns>
        Task<List<ICollectionModel>> GetCollectionsAsync();
    }
}
