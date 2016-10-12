using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiCollectionRepository<TCollection, TDevice, TUser> : IPersistentCollectionRespository<TCollection>
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        Task<bool> AddUserToCollectionAsync(TCollection collection, TUser user);
    }
}
