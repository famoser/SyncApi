using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiRepository<TModel, TCollection, TDevice, TUser> : IPersistentCollectionRespository<TModel>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
    }
}
