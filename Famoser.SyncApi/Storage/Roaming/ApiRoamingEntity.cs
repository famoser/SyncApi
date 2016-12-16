using System;
using Famoser.SyncApi.Enums;

namespace Famoser.SyncApi.Storage.Roaming
{
    public class ApiRoamingEntity
    {
        public Guid UserId { get; set; }
        public int PersonalSeed { get; set; }
        public AuthenticationState AuthenticationState { get; set; }
        public DateTime CreatedAt { get; set; }
    }
}
