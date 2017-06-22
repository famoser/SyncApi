using System.Threading.Tasks;
using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiUserRepository<TUser> : IPersistentRespository<TUser>, IApiUserAuthenticationService
    {
        Task<bool> ReplaceUserAsync(TUser newUser);
    }
}
