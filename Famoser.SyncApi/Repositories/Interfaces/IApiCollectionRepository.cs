using System;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiCollectionRepository<TCollection> : IPersistentCollectionRespository<TCollection>
        where TCollection : ICollectionModel
    {
        Task<bool> AddUserToCollectionAsync(TCollection collection, Guid userId);
    }
}
