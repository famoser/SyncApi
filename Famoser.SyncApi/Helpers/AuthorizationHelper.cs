using System;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Helpers
{
    public class AuthorizationHelper
    {
        public static string GenerateAuthorizationCode(ApiInformationEntity info, ApiRoamingEntity apiRoamingEntity)
        {
            var baseNumber = DateTime.Now.Second + DateTime.Now.Minute*100 + DateTime.Now.Hour*10000 +
                             DateTime.Now.DayOfYear + 1000000;

            var authCode = baseNumber * info.Seed * apiRoamingEntity.PersonalSeed;
            authCode %= info.Modulo;
            return baseNumber + "_" + authCode;
        }
    }
}
