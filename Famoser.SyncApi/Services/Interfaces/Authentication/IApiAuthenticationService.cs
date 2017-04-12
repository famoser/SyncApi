using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Properties;
using Famoser.SyncApi.Repositories.Interfaces;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    /// <summary>
    /// this service is used to authenticate a user and its device against the api
    /// it also creates authenticated requests
    /// </summary>
    public interface IApiAuthenticationService
    {
        /// <summary>
        /// checks if the user is already authenticated
        /// If possible, the user & device is authenticated
        /// </summary>
        /// <returns></returns>
        Task<bool> IsAuthenticatedAsync();

        /// <summary>
        /// gets the device id if already authenticated, does not try to authenticate automatically
        /// </summary>
        /// <returns></returns>
        Guid? TryGetDeviceId();

        /// <summary>
        /// create a valid, authenticated request.
        /// Will only return a request if authenticated
        /// sets:
        ///     - UserId
        ///     - DeviceId
        ///     - AuthenticationCode
        ///     - OnlineAction
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <returns></returns>
        Task<T> CreateRequestAsync<T>(int messageCount = 0) where T : BaseRequest, new();

        /// <summary>
        /// create a valid, authenticated request.
        /// Will only return a request if authenticated
        /// Will get the collectionIds from the corresponding repository
        /// sets:
        ///     - UserId
        ///     - DeviceId
        ///     - AuthenticationCode
        ///     - OnlineAction
        ///     - CollectionIds for action == OnlineAction.SyncVersion
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <typeparam name="TCollection"></typeparam>
        /// <returns></returns>
        Task<T> CreateRequestAsync<T, TCollection>() where T : SyncEntityRequest, new()
             where TCollection : ICollectionModel;

        /// <summary>
        /// creates model information, returns null if initialization from IsAuthenticated is not finished!
        /// sets:
        ///     - Id
        ///     - VersionId
        ///     - CreateDateTime
        ///     - UserId
        ///     - DeviceId
        ///     - sets Create action
        /// </summary>
        /// <returns></returns>
        Task<CacheInformations> CreateModelInformationAsync();

        /// <summary>
        /// Register a collection repository
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <param name="repository"></param>
        void RegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository)
            where TCollection : ICollectionModel;

        /// <summary>
        /// UnRegister a collection repository
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <param name="repository"></param>
        void UnRegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository)
            where TCollection : ICollectionModel;

        /// <summary>
        /// Register a sync repository
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <typeparam name="TSyncModel"></typeparam>
        /// <param name="repository"></param>
        void RegisterRepository<TSyncModel, TCollection>(IApiRepository<TSyncModel, TCollection> repository)
            where TSyncModel : ISyncModel
            where TCollection : ICollectionModel;

        /// <summary>
        /// Register a sync repository
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <typeparam name="TSyncModel"></typeparam>
        /// <param name="repository"></param>
        void UnRegisterRepository<TSyncModel, TCollection>(IApiRepository<TSyncModel, TCollection> repository)
            where TSyncModel : ISyncModel
            where TCollection : ICollectionModel;

        /// <summary>
        /// call this after you've removed an user
        /// </summary>
        /// <returns></returns>
        Task CleanUpAfterUserRemoveAsync();

        /// <summary>
        /// call this after you've removed a device
        /// </summary>
        /// <returns></returns>
        Task CleanUpAfterDeviceRemoveAsync();

        /// <summary>
        /// call this after you've removed a collection
        /// </summary>
        /// <returns></returns>
        Task CleanUpAfterCollectionRemoveAsync<TCollection>(TCollection collection)
            where TCollection : ICollectionModel;
    }
}
