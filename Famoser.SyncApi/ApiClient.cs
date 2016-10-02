using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi
{
    public class ApiClient<TModel>
        where TModel : ISyncModel
    {
        private Uri _baseUri;
        public ApiClient(Uri baseUri)
        {
            _baseUri = baseUri;
        }

        public async Task<bool> EraseDataAsync()
        {

            return true;
        }
    }
}
