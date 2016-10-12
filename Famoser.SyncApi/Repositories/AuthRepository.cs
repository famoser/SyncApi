using Famoser.SyncApi.Repositories.Interfaces;
using Famoser.SyncApi.Services.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public class AuthRepository<TUser, TDevice> : IAuthRepository<TUser, TDevice>, IApiAuthenticationService
    {
        
    }
}
