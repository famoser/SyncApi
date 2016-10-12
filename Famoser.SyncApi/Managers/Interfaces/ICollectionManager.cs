using System.Collections.ObjectModel;

namespace Famoser.SyncApi.Managers.Interfaces
{
    public interface ICollectionManager<TModel>
    {
        ObservableCollection<TModel> GetObservableCollection();
        void Add(TModel model);
        void Remove(TModel model);
        void TransferFrom(ICollectionManager<TModel> collectionManager);
    }
}
