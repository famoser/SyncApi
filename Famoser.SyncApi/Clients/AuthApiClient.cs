using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Clients.Base;
using Famoser.SyncApi.Entities;

namespace Famoser.SyncApi.Clients
{
    public class AuthApiClient : BaseApiClient
    {
        public AuthApiClient(Uri baseUri) : base(baseUri)
        {
        }


        public Task<ResponseEntity> DoRequestAsync(AuthRequestEntity entity)
        {
            return DoApiRequestAsync(entity);
        }
    }
}
