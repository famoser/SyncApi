using System.ComponentModel;
using System.Runtime.CompilerServices;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Properties;

namespace Famoser.SyncApi.Managers
{
    public class Manager<TModel> : IManager<TModel>
    {
        private TModel _model;

        public TModel Model => _model;

        public TModel GetModel()
        {
            return _model;
        }

        public void Set(TModel model)
        {
            bool raisePropertyChanged = !_model.Equals(model);
            _model = model;
            if (raisePropertyChanged)
                OnPropertyChanged(nameof(Model));
        }

        public void TransferFrom(IManager<TModel> manager)
        {
            Set(manager.GetModel());
        }

        public event PropertyChangedEventHandler PropertyChanged;

        [NotifyPropertyChangedInvocator]
        protected virtual void OnPropertyChanged([CallerMemberName] string propertyName = null)
        {
            PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
        }
    }
}
