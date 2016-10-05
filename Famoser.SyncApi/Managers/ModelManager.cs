using System.Collections.ObjectModel;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Managers
{
    public class ModelManager<TModel> : IModelManager<TModel> where TModel : ISyncModel
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
    }
}
