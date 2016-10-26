using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
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
        /// <param name="action"></param>
        /// <returns></returns>
        Task<T> CreateRequestAsync<T>(OnlineAction action) where T : BaseRequest, new();

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
        /// <param name="action"></param>
        /// <returns></returns>
        Task<T> CreateRequestAsync<T, TCollection>(OnlineAction action) where T : SyncEntityRequest, new()
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
        Task<ModelInformation> CreateModelInformationAsync();

        /// <summary>
        /// Regisiter a collection repository, so proper requests for Models can be constructued
        /// </summary>
        /// <typeparam name="TCollection"></typeparam>
        /// <param name="repository"></param>
        void RegisterCollectionRepository<TCollection>(IApiCollectionRepository<TCollection> repository)
            where TCollection : ICollectionModel;
    }
}
