using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;

namespace Famoser.SyncApi.Services.Interfaces
{
    public interface IApiConfigurationService
    {
        /// <summary>
        /// Resolve your api informations
        /// </summary>
        /// <returns></returns>
        ApiInformationEntity GetApiInformations();
        /// <summary>
        /// Return true if the library is allowed to use a web connection
        /// </summary>
        /// <returns></returns>
        bool CanUseWebConnection();
        /// <summary>
        /// Return true if the library should sync immediately after readoing out local storage
        /// </summary>
        /// <returns></returns>
        bool StartSyncAutomatically();
        /// <summary>
        /// Get a user object. This method will be called once per user, while the application is authenticating against the api.
        /// </summary>
        /// <typeparam name="TUser"></typeparam>
        /// <returns></returns>
        Task<TUser> GetUserObjectAsync<TUser>() where TUser : class;
        /// <summary>
        /// Get a device object. This method will be called once for every installation of a user, while the applcation is authenticating against the api
        /// </summary>
        /// <typeparam name="TDevice"></typeparam>
        /// <returns></returns>
        Task<TDevice> GetDeviceObjectAsync<TDevice>() where TDevice : class;
        /// <summary>
        /// Get a collection object. Will be called once if there is no collection present for a specific SyncEntity to be saved
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <returns></returns>
        Task<TCollection> GetCollectionObjectAsync<TCollection>() where TCollection : class;
        /// <summary>
        /// Each filename this library uses passes this function. If you are using the roaming & cache storage too, you may want to modify those so nothing of your data will be overwritten.
        /// </summary>
        /// <param name="proposedFilename"></param>
        /// <param name="objectType"></param>
        /// <returns></returns>
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
