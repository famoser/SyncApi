using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;

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
