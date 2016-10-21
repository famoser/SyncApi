using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Request.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Storage.Cache.Entitites;

namespace Famoser.SyncApi.Services.Interfaces.Authentication
{
    public interface IApiAuthenticationService
    {
        /// <summary>
        /// check if the user is already authenticated
        /// </summary>
        /// <returns></returns>
        bool IsAuthenticated();

        /// <summary>
        /// Authenticate the user against the api
        /// </summary>
        /// <returns></returns>
        Task<bool> AuthenticateAsync();

        /// <summary>
        /// create a valid, authenticated request.
        /// sets:
        ///     - UserId
        ///     - DeviceId
        ///     - AuthenticationCode
        ///     - OnlineAction
        ///     - CollectionIds (if applicable)
        /// </summary>
        /// <typeparam name="T"></typeparam>
        /// <param name="action"></param>
        /// <returns></returns>
        T CreateRequest<T>(OnlineAction action) where T : BaseRequest, new();

        T CreateRequest<T>(OnlineAction action, Type collectionType) where T : SyncEntityRequest, new();

        /// <summary>
        /// creates model information, returns null if device is not / not yet authorized
        /// sets:
        ///     - Id
        ///     - VersionId
        ///     - CreateDateTime
        ///     - UserId
        ///     - DeviceId
        ///     - sets Create action
        /// </summary>
        /// <returns></returns>
        ModelInformation CreateModelInformation();

        void OverwriteCollectionIds<TCollection>(List<Guid> id);
    }
}
