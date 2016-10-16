using System.Threading.Tasks;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IBasePersistentRepository
    {
        Task<bool> SyncAsync();
    }
}
