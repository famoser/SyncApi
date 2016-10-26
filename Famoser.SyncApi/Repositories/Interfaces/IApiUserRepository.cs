using Famoser.SyncApi.Repositories.Interfaces.Base;
using Famoser.SyncApi.Services.Interfaces.Authentication;

namespace Famoser.SyncApi.Repositories.Interfaces
{
    public interface IApiUserRepository<TUser> : IPersistentRespository<TUser>, IApiUserAuthenticationService
    {

    }
}
