using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IBasePersistentRepository
    {
        Task<bool> SyncAsync();
        Task<bool> EraseDataAsync();
    }
}
