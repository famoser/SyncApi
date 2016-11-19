using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services;
using Famoser.SyncApi.Api.Communication.Response.Base;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Api.Base
{
    public class BaseApiClient : IDisposable
    {
        private readonly Uri _baseUri;
        private readonly RestService _restService;

        public BaseApiClient(Uri baseUri)
        {
            _baseUri = baseUri;
            _restService = new RestService();
        }

        private Uri GetUri(string node)
        {
            return new Uri(_baseUri.AbsoluteUri + "1.0/" + node);
        }

        protected virtual async Task<T> DoApiRequestAsync<T>(object request, string node = "") where T : BaseResponse, new()
        {
            var response = await _restService.PostJsonAsync(GetUri(node), JsonConvert.SerializeObject(request));
            var rawResponse = await response.GetResponseAsStringAsync();
            var obj = JsonConvert.DeserializeObject<T>(rawResponse);
            if (obj != null)
            {
                obj.RequestFailed = !response.IsRequestSuccessfull;
                return obj;
            }
            return new T()
            {
                ServerMessage = "Server responded with an " + response.HttpResponseMessage.StatusCode,
                RequestFailed = true
            };
        }

        private bool _isDisposed;
        protected virtual void Dispose(bool disposing)
        {
            if (_isDisposed)
                if (disposing)
                    _restService.Dispose();
            _isDisposed = true;
        }

        public void Dispose()
        {
            Dispose(true);
            GC.SuppressFinalize(this);
        }
    }
}
