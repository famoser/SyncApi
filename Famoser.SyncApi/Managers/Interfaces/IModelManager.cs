using System.Collections.ObjectModel;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Managers.Interfaces
{
    public interface IModelManager<TModel>
    where TModel : ISyncModel
    {
        ObservableCollection<TModel> GetObservableCollection();
        void Add(TModel model);
        void Remove(TModel model);
    }
}
