using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiRepository<TModel, TCollection> : IPersistentCollectionRespository<TModel>
        where TModel : ISyncModel
        where TCollection : ICollectionModel
    {
    }
}
