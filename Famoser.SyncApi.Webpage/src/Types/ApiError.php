<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 13:23
 */

namespace Famoser\SyncApi\Types;


class ApiError
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

    #region general error
//[Description("Cannot create something which already exists")]
    const ResourceAlreadyExists = 200;
    #endregion

    #region AuthorisationRequest
//[Description("Your authorization code is invalid")]
    const AuthorizationCodeInvalid = 2000;
    #endregion
}