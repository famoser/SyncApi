using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services;
using Famoser.SyncApi.Clients.Base;
using Famoser.SyncApi.Entities;
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

        protected Task<ResponseEntity> DoApiRequestAsync(RequestEntity request)
        {
            request.UserId = _userId;
            request.DeviceId = _deviceId;
            return base.DoApiRequestAsync(request);
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
    }
}
