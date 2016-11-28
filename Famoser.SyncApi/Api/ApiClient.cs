using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Base;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Response;

namespace Famoser.SyncApi.Api
{
    public class ApiClient : BaseApiClient
    {
        public ApiClient(Uri baseUri) : base(baseUri)
        {
        }

        /// <summary>
        /// sync collections
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<CollectionEntityResponse> DoSyncRequestAsync(CollectionEntityRequest entity)
        {
            return DoApiRequestAsync<CollectionEntityResponse>(entity, "collections/sync");
        }

        /// <summary>
        /// sync collections
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<HistoryEntityResponse> DoEntityHistoryRequestAsync(HistoryEntityRequest entity)
        {
            return DoApiRequestAsync<HistoryEntityResponse>(entity, "entities/history/sync");
        }

        /// <summary>
        /// sync entities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<SyncEntityResponse> DoSyncRequestAsync(SyncEntityRequest entity)
        {
            return DoApiRequestAsync<SyncEntityResponse>(entity, "entities/sync");
        }

        /// <summary>
        /// sync the devices & users in the lists
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> DoSyncRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/sync");
        }

        /// <summary>
        /// Create an authorization code, authenticated Device in DeviceId
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> CreateAuthorizationCodeAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/generate");
        }

        /// <summary>
        /// use an authorization code for own DeviceId
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> UseAuthenticationCodeAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/use");
        }

        /// <summary>
        /// authenticate the DeviceEntities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> AuthenticateUserRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "users/auth");
        }

        /// <summary>
        /// Get all devices from an user
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<CollectionEntityResponse> GetDevicesAsync(CollectionEntityRequest entity)
        {
            return DoApiRequestAsync<CollectionEntityResponse>(entity, "devices/get");
        }

        /// <summary>
        /// authenticate the DeviceEntities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> AuthenticateDeviceAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "devices/auth");
        }

        /// <summary>
        /// unauthenticate the DeviceEntities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> UnAuthenticateDeviceAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "devices/unauth");
        }
    }
}
