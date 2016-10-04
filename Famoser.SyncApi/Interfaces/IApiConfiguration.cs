using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.SyncApi.Entities.Storage.Cache;
using Famoser.SyncApi.Entities.Storage.Roaming;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiConfiguration
    {
        Task<Uri> GetApiUri();
        Task<object> GetDeviceObjectAsync();
        Task<object> GetUserObjectAsync();
    }
}
