using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces;
using Famoser.SyncApi.Repositories.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public class AuthRepository<TUser, TDevice> : IAuthRepository<TUser, TDevice>
}
