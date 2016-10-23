using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Managers.Interfaces;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IPersistentCollectionRespository<T> : IBasePersistentRepository
    {
        ObservableCollection<T> GetAllLazy();
        Task<ObservableCollection<T>> GetAllAsync();

        Task<bool> SaveAsync(T model);
        Task<bool> RemoveAsync(T model);
        ObservableCollection<T> GetHistoryLazy(T model);
        Task<ObservableCollection<T>> GetHistoryAsync(T model);

        void SetCollectionManager(ICollectionManager<T> manager);
        ICollectionManager<T> GetCollectionManager();
    }
}
