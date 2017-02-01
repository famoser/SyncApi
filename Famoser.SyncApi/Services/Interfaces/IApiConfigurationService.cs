using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Managers.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces
{
    /// <summary>
    /// This service configures the api
    /// </summary>
    public interface IApiConfigurationService
    {
        /// <summary>
        /// Resolve your api informations
        /// </summary>
        /// <returns></returns>
        ApiInformation GetApiInformations();
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
        /// Get a collection object. Will be called once if there is no collection present for a specific SyncEntities to be saved
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <returns></returns>
        Task<TCollection> GetCollectionObjectAsync<TCollection>() where TCollection : class;
        /// <summary>
        /// Construct a collection manager for the specified model
        /// </summary>
        /// <typeparam name="TModel"></typeparam>
        /// <returns></returns>
        ICollectionManager<TModel> GetCollectionManager<TModel>();
        /// <summary>
        /// Contruct a manager for the speicified model
        /// </summary>
        /// <typeparam name="TModel"></typeparam>
        /// <returns></returns>
        IManager<TModel> GetManager<TModel>();
        /// <summary>
        /// Each filename this library uses passes this function. If you are using the roaming & cache storage too, you may want to modify those so nothing of your data will be overwritten.
        /// </summary>
        /// <param name="proposedFilename"></param>
        /// <param name="objectType"></param>
        /// <returns></returns>
        string GetFileName(string proposedFilename, Type objectType = null);
    }
}
