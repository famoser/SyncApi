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


        public Task<AuthorizationResponse> DoRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync<AuthorizationResponse>(entity);
        }
    }
}
