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
    const None = 0;

    const ApplicationNotFound = 900;

    const UserNotFound = 1000;
    const UserNotAuthorized = 1000;

    const DeviceNotFound = 1002;
    const DeviceNotAuthorized = 1001;
    const DeviceUnAuthorized = 1003;

    const ResourceAlreadyExists = 200;
    const AuthorizationCodeInvalid = 2000;

    public static function toString($apiError)
    {
        switch ($apiError) {
            case ApiError::None:
                return "no error occurred";
            case ApiError::ApplicationNotFound:
                return "application not found";
            case ApiError::UserNotFound:
                return "user not found";
            case ApiError::UserNotAuthorized:
                return "user not authorized";
            case ApiError::DeviceNotFound:
                return "device not found";
            case ApiError::DeviceNotAuthorized:
                return "device not authorized";
            case ApiError::DeviceUnAuthorized:
                return "device unauthorized";
            case ApiError::ResourceAlreadyExists:
                return "resource already exists";
            case ApiError::AuthorizationCodeInvalid:
                return "authorization code invalid";
            default:
                return "unknown api error occurred with code " . $apiError;
        }
    }
}