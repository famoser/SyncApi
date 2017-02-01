using System;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IBasePersistentRepository : IDisposable
    {
        Task<bool> SyncAsync();
    }
}
