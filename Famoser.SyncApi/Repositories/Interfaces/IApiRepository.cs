using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiRepository<TModel> : IPersistentCollectionRespository<TModel>
        where TModel : ISyncModel
    {
    }
}
