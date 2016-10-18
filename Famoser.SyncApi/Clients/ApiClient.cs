using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Api.Communication.Request;
using Famoser.SyncApi.Api.Communication.Response;
using Famoser.SyncApi.Clients.Base;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Clients
{
    public class ApiClient : BaseApiClient
    {
        private readonly Guid _userId;
        private readonly Guid _deviceId;

        public ApiClient(Uri baseUri, Guid userId, Guid deviceId) : base(baseUri)
        {
            _userId = userId;
            _deviceId = deviceId;
        }


        public Task<SyncEntityResponse> DoApiRequestAsync(SyncEntityRequest request)
        {
            request.UserId = _userId;
            request.DeviceId = _deviceId;
            return base.DoApiRequestAsync<SyncEntityResponse>(request);
        }

        public Task<TReq> DoApiRequestAsync<TResp, TReq>(TReq request)
        {
            request.UserId = _userId;
            request.DeviceId = _deviceId;
            return base.DoApiRequestAsync<TResp>(request);
        }


        public async Task<bool> EraseDataAsync()
        {
            var res = await DoApiRequestAsync(new CollectionEntityRequest()
            {
                OnlineAction = OnlineAction.Erase
            });
            return !res.RequestFailed;
        }

        public Task<ResponseEntity> DoRequestAsync(CollectionEntityRequest entity)
        {
            return DoApiRequestAsync(entity);
        }
    }
}
