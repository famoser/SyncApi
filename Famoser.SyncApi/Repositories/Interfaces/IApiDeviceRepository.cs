using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces.Base;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiDeviceRepository<TDevice, TUser> : IPersistentRespository<TDevice>
        where TDevice : IDeviceModel
        where TUser : IUserModel
    {
        ObservableCollection<TDevice> GetAllLazy();
        Task<ObservableCollection<TDevice>> GetAll();

        Task<bool> UnAuthenticateAsync(TDevice device);
        Task<bool> AuthenticateAsync(TDevice device);

        Task<string> CreateNewAuthenticationCodeAsync();
        Task<bool> TryUseAuthenticationCodeAsync(string authenticationCode);
    }
}
