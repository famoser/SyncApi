using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Services;
using Famoser.SyncApi.Entities;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;
using Newtonsoft.Json;

namespace Famoser.SyncApi
{
    public class ApiClient<TModel>
        where TModel : ISyncModel
    {
        private readonly Uri _baseUri;
        private readonly Guid _userId;
        private readonly RestService _restService;
        public ApiClient(Uri baseUri, Guid userId)
        {
            _baseUri = baseUri;
            _userId = userId;
            _restService = new RestService();
        }

        private Uri GetUri()
        {
            return _baseUri;
        }

        private async Task<ResponseEntity> DoApiRequestAsync(RequestEntity request)
        {
            request.UserId = _userId;
            var response = await _restService.PostJsonAsync(GetUri(), JsonConvert.SerializeObject(request));
            var rawResponse = await response.GetResponseAsStringAsync();
            var obj = JsonConvert.DeserializeObject<ResponseEntity>(rawResponse);
            if (obj != null)
            {
                obj.RequestFailed = !response.IsRequestSuccessfull;
                return obj;
            }
            return new ResponseEntity()
            {
                RequestFailed = true
            };
        }


        public async Task<bool> EraseDataAsync()
        {
            var res = await DoApiRequestAsync(new RequestEntity()
            {
                OnlineAction = OnlineAction.Erase
            });
            return !res.RequestFailed;
        }

        public Task<ResponseEntity> DoRequestAsync(RequestEntity entity)
        {
            return DoApiRequestAsync(entity);
        }

        public Task<bool> CreateAsync(TModel model, Guid collectionId)
        {
            throw new NotImplementedException();
        }

        public Task<bool> UpdateAsync(ISyncModel model, Guid collectionId)
        {
            throw new NotImplementedException();
        }
    }
}
