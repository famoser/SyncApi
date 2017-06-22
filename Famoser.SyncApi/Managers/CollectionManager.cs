using System.Collections.ObjectModel;
using Famoser.SyncApi.Managers.Interfaces;

namespace Famoser.SyncApi.Managers
{
    public class CollectionManager<TModel> : ICollectionManager<TModel>
    {
        private readonly ObservableCollection<TModel> _collection = new ObservableCollection<TModel>();
        public ObservableCollection<TModel> GetObservableCollection()
        {
            return _collection;
        }

        public void Add(TModel model)
        {
            _collection.Add(model);
        }

        public void Remove(TModel model)
        {
            _collection.Remove(model);
        }

        public void TransferFrom(ICollectionManager<TModel> collectionManager)
        {
            foreach (var model in collectionManager.GetObservableCollection())
            {
                Add(model);
            }
        }

        public void Replace(TModel oldOne, TModel newOne)
        {
           var index = _collection.IndexOf(oldOne);
            _collection[index] = newOne;
        }

        public void Clear()
        {
            _collection.Clear();
        }
    }
}
