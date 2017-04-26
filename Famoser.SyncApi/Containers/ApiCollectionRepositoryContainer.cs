using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;

namespace Famoser.SyncApi.Containers
{
    internal class ApiCollectionRepositoryContainer
    {
        private readonly Dictionary<Type, object> _internal = new Dictionary<Type, object>();

        public void Add<TCollection>(IApiCollectionRepository<TCollection> repo)
            where TCollection : ICollectionModel
        {
            _internal.Add(typeof(TCollection), repo);
        }

        public bool Contains<TCollection>()
            where TCollection : ICollectionModel
        {
            return _internal.ContainsKey(typeof(TCollection));
        }

        public void Remove<TCollection>()
            where TCollection : ICollectionModel
        {
            _internal.Remove(typeof(TCollection));
        }

        public IApiCollectionRepository<TCollection> Get<TCollection>()
            where TCollection : ICollectionModel
        {
            return _internal[typeof(TCollection)] as IApiCollectionRepository<TCollection>;
        }

        public List<object> GetAll()
        {
            return _internal.Values.ToList();
        }
    }
}
