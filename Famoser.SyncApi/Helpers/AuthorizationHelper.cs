using System;
using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Helpers
{
    public class AuthorizationHelper
    {
        private static Random _random = new Random((int)DateTime.Now.Ticks);
        public static string GenerateAuthorizationCode(ApiInformation info, ApiRoamingEntity apiRoamingEntity)
        {
            var rand = _random.Next(0, 99);
            var seconds = DateTime.Now.Second;
            var minutes = DateTime.Now.Minute;
            var hours = DateTime.Now.Hour;
            var baseStringNumber = seconds.ToString("00") + minutes.ToString("00") + hours.ToString("00") + rand.ToString("00");

            var baseNumber = seconds + minutes * 100 + hours * 10000 + rand;

            long authCode = baseNumber * info.ApplicationSeed * apiRoamingEntity.PersonalSeed;
            authCode %= info.Modulo;
            return baseStringNumber + "_" + authCode;
        }
    }
}
