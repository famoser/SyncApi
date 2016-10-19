using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Helpers;
using Famoser.SyncApi.Managers;
using Famoser.SyncApi.Managers.Interfaces;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public class ApiCollectionRepository<TCollection, TDevice, TUser> : BaseHelper, IApiCollectionRepository<TCollection, TDevice, TUser>
        where TCollection : ICollectionModel
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        private CollectionManager<TCollection> _collectionManager = new CollectionManager<TCollection>();

        public Task<bool> AddUserToCollectionAsync(TCollection collection, TUser user)
        {
            throw new NotImplementedException();
        }

        public Task<bool> SyncAsync()
        {
            throw new NotImplementedException();
        }

        public ObservableCollection<TCollection> GetAllLazy()
        {
            throw new NotImplementedException();
        }

        public Task<ObservableCollection<TCollection>> GetAll()
        {
            throw new NotImplementedException();
        }

        public Task<bool> SaveAsync(TCollection model)
        {
            throw new NotImplementedException();
        }

        public Task<bool> RemoveAsync(TCollection model)
        {
            throw new NotImplementedException();
        }

        public Task<bool> RemoveAllAsync()
        {
            throw new NotImplementedException();
        }

        public void SetCollectionManager(ICollectionManager<TCollection> manager)
        {
            throw new NotImplementedException();
        }

        public ICollectionManager<TCollection> GetCollectionManager()
        {
            throw new NotImplementedException();
        }
    }
}
