using System;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiCollectionRepository<TCollection> : IPersistentRespository<TCollection>
        where TCollection : ICollectionModel
    {
    }
}
