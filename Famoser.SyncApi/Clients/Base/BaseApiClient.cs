using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Entities.Base;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Clients.Base
{
    public class BaseApiClient
    {
        private readonly Uri _baseUri;
        private readonly RestService _restService;

        public BaseApiClient(Uri baseUri)
        {
            _baseUri = baseUri;
            _restService = new RestService();
        }

        private Uri GetUri()
        {
            return _baseUri;
        }

        protected virtual async Task<T> DoApiRequestAsync<T>(object request) where T : BaseResponse, new()
        {
            var response = await _restService.PostJsonAsync(GetUri(), JsonConvert.SerializeObject(request));
            var rawResponse = await response.GetResponseAsStringAsync();
            var obj = JsonConvert.DeserializeObject<T>(rawResponse);
            if (obj != null)
            {
                obj.RequestFailed = !response.IsRequestSuccessfull;
                return obj;
            }
            return new T()
            {
                RequestFailed = true
            };
        }
    }
}
