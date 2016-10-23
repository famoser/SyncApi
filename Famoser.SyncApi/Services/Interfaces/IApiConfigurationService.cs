using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;

namespace Famoser.SyncApi.Services.Interfaces
{
    /// <summary>
    /// configures the api
    /// </summary>
    public interface IApiConfigurationService
    {
        /// <summary>
        /// configure the api to be used by this wrapper
        /// </summary>
        /// <returns></returns>
        ApiInformationEntity GetApiInformations();
        /// <summary>
        /// Get a device object. This will represent the current installation.
        /// </summary>
        /// <typeparam name="TDevice"></typeparam>
        /// <returns></returns>
        Task<TDevice> GetDeviceObjectAsync<TDevice>();
        /// <summary>
        /// Get a user object. This will be synced across all devices of the same user
        /// </summary>
        /// <typeparam name="TUser"></typeparam>
        /// <returns></returns>
        Task<TUser> GetUserObjectAsync<TUser>(); 
        /// <summary>
        /// normally you may just return the proposed filename.
        /// if you have conflicts (for example if you use multiple instances of the API) you may customize the name
        /// </summary>
        /// <param name="proposedFilename"></param>
        /// <param name="objectType"></param>
        /// <returns></returns>
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
