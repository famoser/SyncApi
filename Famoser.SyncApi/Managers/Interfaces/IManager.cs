using System.ComponentModel;

namespace Famoser.SyncApi.Managers.Interfaces
{
    public interface IManager<TModel> : INotifyPropertyChanged
    {
        TModel GetModel();
        void Set(TModel model);
        void TransferFrom(IManager<TModel> manager);
    }
}
