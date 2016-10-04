using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Interfaces
{
    public interface IApiRepository<TModel>
        where TModel : ISyncModel
    {
        ObservableCollection<TModel> GetAll();
        Task<bool> Sync();
        Task<bool> Save(TModel model);
        Task<bool> Remove(TModel model);
        Task<bool> EraseData();
    }
}
