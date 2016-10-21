using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Response;
using Famoser.SyncApi.Clients.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Services.Interfaces;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Clients
{
    public class ApiClient : BaseApiClient
    {
        private readonly IApiAuthenticationService _apiAuthenticationService;
        public ApiClient(Uri baseUri, IApiAuthenticationService apiAuthenticationService) : base(baseUri)
        {
            _apiAuthenticationService = apiAuthenticationService;
        }

        public Task<SyncEntityResponse> DoApiRequestAsync(SyncEntityRequest request)
        {
            _apiAuthenticationService.AuthenticateRequest(request);
            return base.DoApiRequestAsync<SyncEntityResponse>(request);
        }
        
    }
}
