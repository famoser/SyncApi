using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiRepository<TModel, in TCollection> : IPersistentCollectionRespository<TModel>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
    {
        Task<bool> SaveAsync(TModel model);
        Task<bool> SaveToCollectionAsync(TModel model, TCollection collection);
        Task<bool> RemoveAllFromCollectionAsync(TCollection collection);
    }
}
