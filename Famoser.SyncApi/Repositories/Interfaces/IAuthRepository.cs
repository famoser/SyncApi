using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IAuthRepository<TUser, TDevice>
           where TUser : IUserModel
           where TDevice : IDeviceModel
    {
        Task<bool> Initialize();
        TUser GetUser();
        Task<bool> SetUserAsync(TUser user);
        TDevice GetDevice();
        Task<bool> SetDeviceAsync(TDevice device);
        Task<bool> IsAuthorizedAsync();
    }
}
