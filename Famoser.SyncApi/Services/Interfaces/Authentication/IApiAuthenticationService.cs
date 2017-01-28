using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Models.Information;
using Famoser.SyncApi.Models.Interfaces;
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
        /// Regisiter a collection repository, so proper requests for Models can be constructued
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <param name="repository"></param>
        void RegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository)
            where TCollection : ICollectionModel;

        /// <summary>
        /// set the service to use for device authenticaion
        /// </summary>
        /// <param name="userAuthenticationService"></param>
        void SetUserAuthenticationService(IApiUserAuthenticationService userAuthenticationService);

        /// <summary>
        /// set the service to use for user authentication
        /// </summary>
        /// <param name="deviceAuthenticationService"></param>
        void SetDeviceAuthenticationService(IApiDeviceAuthenticationService deviceAuthenticationService);
    }
}
