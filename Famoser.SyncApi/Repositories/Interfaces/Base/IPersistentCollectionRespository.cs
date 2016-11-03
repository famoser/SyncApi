using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Information;

namespace Famoser.SyncApi.Repositories.Interfaces.Base
{
    public interface IPersistentCollectionRespository<T> : IBasePersistentRepository
    {
        ObservableCollection<T> GetAllLazy();
        Task<ObservableCollection<T>> GetAllAsync();

        Task<bool> SaveAsync(T model);
        Task<bool> RemoveAsync(T model);
        ObservableCollection<HistoryInformations<T>> GetHistoryLazy(T model);
        Task<ObservableCollection<HistoryInformations<T>>> GetHistoryAsync(T model);
        Task<bool> SyncHistoryAsync(T model);
        CacheInformations GetCacheInformations(T model);

        void SetCollectionManager(ICollectionManager<T> manager);
        ICollectionManager<T> GetCollectionManager();
    }
}
