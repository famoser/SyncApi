using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiCollectionRepository<TCollection> : IPersistentCollectionRespository<TCollection>
        where TCollection : ICollectionModel
    {
        /// <summary>
        /// add an user to a collection; effectively giving him read/write access to the content of the collection
        /// </summary>
        /// <param name="collection"></param>
        /// <param name="userModel"></param>
        /// <returns></returns>
        Task<bool> AddUserToCollectionAsync(TCollection collection, IUserModel userModel);
        Task<bool> SaveAsync(TCollection model);
        Task<TCollection> GetDefaultCollection();
    }
}
