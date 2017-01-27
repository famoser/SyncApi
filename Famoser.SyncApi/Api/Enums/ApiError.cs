using Famoser.FrameworkEssentials.Attributes;

namespace Famoser.SyncApi.Api.Enums
{
    public enum ApiError
    {
        [Description("no error occurred")]
        None = 0,

        [Description("application not found")]
        ApplicationNotFound = 1000,

        [Description("user not found")]
        UserNotFound = 2000,
        [Description("user removed")]
        UserRemoved = 2001,
        [Description("user not authorized")]
        UserNotAuthorized = 2002,

        [Description("user seed messing")]
        PersonalSeedMissing = 2003,
        [Description("user seed not numeric")]
        PersonalSeedNotNummeric = 2004,
        [Description("user seed too small")]
        PersonalSeedTooSmall = 2005,

        [Description("device not found")]
        DeviceNotFound = 3000,
        [Description("device not authorized")]
        DeviceRemoved = 3001,
        [Description("device unauthorized")]
        DeviceNotAuthorized = 3002,
        [Description("device removed")]
        DeviceUnAuthorized = 3003,

        [Description("resource already exists")]
        ResourceAlreadyExists = 4000,
        [Description("resource not found")]
        ResourceNotFound = 4001,

        [Description("authorization code invalid")]
        AuthorizationCodeInvalid = 5000,

        [Description("action not supported")]
        ActionNotSupported = 6000,
        [Description("action prohibited")]
        ActionProhibided = 6001,

        [Description("unknown server error occurred")]
        ServerError = 7000,
        [Description("url is not available")]
        NoteNotFound = 7001,
        [Description("method is not allowed")]
        MethodNotAllowed = 7002
    }
}
