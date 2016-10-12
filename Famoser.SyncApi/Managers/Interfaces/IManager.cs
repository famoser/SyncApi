using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Managers.Interfaces
{
    public interface IManager<TModel> : INotifyPropertyChanged
    {
        TModel GetModel();
        void Set(TModel model);
        void TransferFrom(IManager<TModel> manager);
    }
}
