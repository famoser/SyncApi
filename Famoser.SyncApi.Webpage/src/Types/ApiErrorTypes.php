<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 13:23
 */

namespace Famoser\SyncApi\Types;


class ApiErrorTypes
{
//[Description("No API error occured")]
    const None = 0;

    #region request errors
//[Description("Api Version unknown")]
    const ApiVersionInvalid = 100;

//[Description("Json request could not be deserialized")]
    const RequestJsonFailure = 101;

//[Description("Request could not be processed by the server. This is probably a API error, nothing you can do about it :/")]
    const ServerFailure = 102;

//[Description("Json request could not be deserialized")]
    const RequestUriInvalid = 103;

//[Description("Execution of request was denied")]
    const Forbidden = 104;

//[Description("Some required properties were missing")]
    const NotWellDefined = 105;

//[Description("A failure occured on the server while accessing the database")]
    const DatabaseFailure = 106;
    #endregion

    #region general errors
//[Description("Your device is unknown to the API")]
    const NotAuthorized = 1000;

//[Description("Your device was unauthorized")]
    const Unauthorized = 1001;
    #endregion

    #region CreateUserRequest
//[Description("This User already exists")]
    const UserAlreadyExists = 2100;
    #endregion

    #region AuthorisationRequest
//[Description("Your athorization code is invalid")]
    const AuthorizationCodeInvalid = 2000;
//[Description("User not found")]
    const UserNotFound = 2001;
//[Description("Device already exists")]
    const DeviceAlreadyExists = 2002;
    #endregion

    #region UnAuthorisationRequest
//[Description("The device to unauthorize could not be found")]
    const DeviceNotFound = 3000;
    #endregion

    #region AuthorizedDevicesRequst
//[Description("No authorized devices could be found")]
    const NoDevicesFound = 4000;
    #endregion

    #region ReadContentEntityRequest
//[Description("Content not found")]
    const ContentNotFound = 5000;
    #endregion

    #region UpdateRequest
//[Description("Submitted Version is not newest one")]
    const InvalidVersionId = 6000;
    #endregion
}