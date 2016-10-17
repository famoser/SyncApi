using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Response;
using Famoser.SyncApi.Clients.Base;

namespace Famoser.SyncApi.Clients
{
    public class AuthApiClient : BaseApiClient
    {
        public AuthApiClient(Uri baseUri) : base(baseUri)
        {
        }


        public Task<AuthorizationResponse> DoSyncRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "sync");
        }

        public Task<AuthorizationResponse> CreateAuthCodeRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "auth");
        }

        public Task<AuthorizationResponse> UseAuthCodeRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "useauth");
        }

        public Task<AuthorizationResponse> AuthenticateRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "doauth");
        }

        public Task<AuthorizationResponse> UnAuthenticateRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity, "unauth");
        }
    }
}
