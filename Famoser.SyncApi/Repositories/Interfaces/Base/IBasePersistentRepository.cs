using System;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Logging.Interfaces;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IBasePersistentRepository : IDisposable
    {
        Task<bool> SyncAsync();

        void SetExceptionLogger(IExceptionLogger exceptionLogger);
    }
}
