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
    const NONE = 0;

    const APPLICATION_NOT_FOUND = 900;

    const USER_REMOVED = 1000;
    const USER_NOT_FOUND = 1000;
    const PERSONAL_SEED_MISSING = 1000;
    const PERSONAL_SEED_NOT_NUMERIC = 1000;
    const PERSONAL_SEED_TOO_SMALL = 1000;

    const DEVICE_NOT_FOUND = 1002;
    const DEVICE_NOT_AUTHORIZED = 1001;
    const DEVICE_UNAUTHORIZED = 1003;
    const DeviceRemoved = 1002;

    const RESOURCE_ALREADY_EXISTS = 200;
    const RESOURCE_NOT_FOUND = 200;
    const AUTHORIZATION_CODE_INVALID = 2000;

    const ACTION_NOT_SUPPORTED = 3000;

    const ACTION_PROHIBITED = 3000;

    public static function toString($apiError)
    {
        switch ($apiError) {
            case ApiError::NONE:
                return "no error occurred";
            case ApiError::APPLICATION_NOT_FOUND:
                return "application not found";
            case ApiError::USER_NOT_FOUND:
                return "user not found";
            case ApiError::DEVICE_NOT_FOUND:
                return "device not found";
            case ApiError::DEVICE_NOT_AUTHORIZED:
                return "device not authorized";
            case ApiError::DEVICE_UNAUTHORIZED:
                return "device unauthorized";
            case ApiError::RESOURCE_ALREADY_EXISTS:
                return "resource already exists";
            case ApiError::AUTHORIZATION_CODE_INVALID:
                return "authorization code invalid";
            case ApiError::ACTION_NOT_SUPPORTED:
                return "action not supported";
            default:
                return "unknown api error occurred with code " . $apiError;
        }
    }
}
