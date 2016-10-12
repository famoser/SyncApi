using System.Threading.Tasks;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IPersistentRespository<T> : IBasePersistentRepository
    {
        Task<T> GetAsync();
        Task<bool> SaveAsync();
        Task<bool> RemoveAsync();
    }
}
