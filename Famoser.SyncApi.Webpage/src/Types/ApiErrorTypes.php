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

    #region auth
//[Description("You are not authorized at the API")]
    const UserNotAuthorized = 1000;
//[Description("Your device is not authorized at the API")]
    const DeviceNotAuthorized = 1001;
//[Description("Your device was unauthorized")]
    const DeviceUnAuthorized = 1002;
    #endregion

    #region request errors
//[Description("Json request could not be deserialized")]
    const RequestJsonFailure = 101;
//[Description("You tried to access a resource you have no access to")]
    const Forbidden = 104;
//[Description("Some required properties were missing")]
    const NotWellDefined = 105;
    #endregion

    #region general error
//[Description("Cannot create something which already exists")]
    const ResourceAlreadyExists = 200;
    #endregion

    #region AuthorisationRequest
//[Description("Your authorization code is invalid")]
    const AuthorizationCodeInvalid = 2000;
    #endregion
}