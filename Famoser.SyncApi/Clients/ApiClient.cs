using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Response;
using Famoser.SyncApi.Clients.Base;

namespace Famoser.SyncApi.Clients
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
            return DoApiRequestAsync<CollectionEntityResponse>(entity, "collection/sync");
        }
        
        /// <summary>
        /// sync entities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<SyncEntityResponse> DoSyncRequestAsync(SyncEntityRequest entity)
        {
            return DoApiRequestAsync<SyncEntityResponse>(entity, "entity/sync");
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
        public Task<AuthorizationResponse> CreateAuthCodeRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/generate");
        }

        /// <summary>
        /// use an authorization code for own DeviceId
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> UseAuthCodeRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/use");
        }

        /// <summary>
        /// authenticate the DeviceEntities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> AuthenticateRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/do");
        }

        /// <summary>
        /// unauthenticate the DeviceEntities
        /// </summary>
        /// <param name="entity"></param>
        /// <returns></returns>
        public Task<AuthorizationResponse> UnAuthenticateRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth/undo");
        }
    }
}
