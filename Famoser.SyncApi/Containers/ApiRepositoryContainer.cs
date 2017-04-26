using System;
using System.Collections.Generic;
using System.Linq;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;

namespace Famoser.SyncApi.Containers
{
    internal class ApiRepositoryContainer
    {
        private readonly Dictionary<Type, List<Tuple<Type, object>>> _internal = new Dictionary<Type, List<Tuple<Type, object>>>();

        public void Add<TModel, TCollection>(IApiRepository<TModel, TCollection> repo)
            where TCollection : ICollectionModel where TModel : ISyncModel
        {
            if (!_internal.ContainsKey(typeof(TCollection)))
                _internal.Add(typeof(TCollection), new List<Tuple<Type, object>>()); ;

            _internal[typeof(TCollection)].Add(new Tuple<Type, object>(typeof(TModel), repo));
        }

        public void Remove<TModel, TCollection>()
            where TCollection : ICollectionModel where TModel : ISyncModel
        {
            if (_internal.ContainsKey(typeof(TCollection)))
            {
                for (var index = 0; index < _internal[typeof(TCollection)].Count; index++)
                {
                    var o = _internal[typeof(TCollection)][index];
                    if (o.Item1 == typeof(TModel))
                    {
                        _internal[typeof(TCollection)].Remove(o);
                        index--;
                    }
                }
            }
        }

        public void Get<TModel, TCollection>()
            where TCollection : ICollectionModel where TModel : ISyncModel
        {
            if (_internal.ContainsKey(typeof(TCollection)))
            {
                for (var index = 0; index < _internal[typeof(TCollection)].Count; index++)
                {
                    var o = _internal[typeof(TCollection)][index];
                    if (o.Item1 == typeof(TModel))
                    {
                        _internal[typeof(TCollection)].Remove(o);
                        index--;
                    }
                }
            }
        }

        public IEnumerable<object> GetAll<TCollection>()
            where TCollection : ICollectionModel
        {
            if (_internal.ContainsKey(typeof(TCollection)))
            {
                return _internal[typeof(TCollection)].Select(e => e.Item2);
            }
            return new object[0];
        }

        public IEnumerable<object> GetAll()
        {
            return _internal.SelectMany(s => s.Value.Select(e => e.Item2));
        }
    }
}
