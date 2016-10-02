using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics.Contracts;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
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
