using Famoser.SyncApi.Api.Configuration;
using Famoser.SyncApi.Storage.Roaming;

namespace Famoser.SyncApi.Helpers
{
    public class AuthorizationHelper
    {
        public static string GenerateAuthorizationCode(ApiInformation info, ApiRoamingEntity apiRoamingEntity, int requestMagicNumber = 0)
        {
            var authCode = apiRoamingEntity.PersonalSeed * apiRoamingEntity.RequestCount + requestMagicNumber * info.ApplicationSeed;
            return (authCode % info.ApiModulo).ToString();
        }
    }
}
