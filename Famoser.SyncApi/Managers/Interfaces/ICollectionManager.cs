using System.Collections.ObjectModel;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Managers.Interfaces
{
    public interface ICollectionManager<TModel>
    {
        ObservableCollection<TModel> GetObservableCollection();
        void Add(TModel model);
        void Remove(TModel model);
        void TransferFrom(ICollectionManager<TModel> collectionManager);
        void Replace(TModel oldOne, TModel newOne);
    }
}
