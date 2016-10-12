using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Annotations;
using Famoser.SyncApi.Managers.Interfaces;

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
