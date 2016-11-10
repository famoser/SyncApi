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
    const UserRemoved = 1000;
    const PersonalSeedMissing = 1000;
    const PersonalSeedNotNumeric = 1000;
    const PersonalSeedTooSmall = 1000;

    const DeviceNotFound = 1002;
    const DeviceRemoved = 1002;
    const DeviceNotAuthorized = 1001;
    const DeviceUnAuthorized = 1003;

    const ResourceAlreadyExists = 200;
    const ResourceNotFound = 200;
    const AuthorizationCodeInvalid = 2000;

    const ActionNotSupported = 3000;

    public static function toString($apiError)
    {
        switch ($apiError) {
            case ApiError::None:
                return "no error occurred";
            case ApiError::ApplicationNotFound:
                return "application not found";
            case ApiError::UserNotFound:
                return "user not found";
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
            case ApiError::ActionNotSupported:
                return "action not supported";
            default:
                return "unknown api error occurred with code " . $apiError;
        }
    }
}