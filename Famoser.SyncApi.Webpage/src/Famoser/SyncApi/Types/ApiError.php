<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 13:23
 */

namespace Famoser\SyncApi\Types;

/**
 * any error which occurs by processing an api request
 *
 * @package Famoser\SyncApi\Types
 */
class ApiError
{
    const NONE = 0;

    const APPLICATION_NOT_FOUND = 1000;

    const USER_NOT_FOUND = 2000;
    const USER_REMOVED = 2001;
    const USER_NOT_AUTHORIZED = 2002;

    const PERSONAL_SEED_MISSING = 2003;
    const PERSONAL_SEED_NOT_NUMERIC = 2004;
    const PERSONAL_SEED_TOO_SMALL = 2005;

    const DEVICE_NOT_FOUND = 3000;
    const DEVICE_REMOVED = 3001;
    const DEVICE_NOT_AUTHORIZED = 3002;
    const DEVICE_UNAUTHORIZED = 3003;

    const RESOURCE_ALREADY_EXISTS = 4000;
    const RESOURCE_NOT_FOUND = 4001;

    const AUTHORIZATION_CODE_INVALID = 5000;

    const ACTION_NOT_SUPPORTED = 6000;
    const ACTION_PROHIBITED = 6001;

    const SERVER_ERROR = 7000;
    const NODE_NOT_FOUND = 7001;
    const METHOD_NOT_ALLOWED = 7002;

    /**
     * convert the api to a string
     *
     * @param $apiError
     * @return string
     */
    public static function toString($apiError)
    {
        switch ($apiError) {
            case ApiError::NONE:
                return "no error occurred";
            case ApiError::APPLICATION_NOT_FOUND:
                return "application not found";

            case ApiError::USER_NOT_FOUND:
                return "user not found";
            case ApiError::USER_REMOVED:
                return "user removed";
            case ApiError::USER_NOT_AUTHORIZED:
                return "user not authorized";

            case ApiError::PERSONAL_SEED_MISSING:
                return "user seed messing";
            case ApiError::PERSONAL_SEED_NOT_NUMERIC:
                return "user seed not numeric";
            case ApiError::PERSONAL_SEED_TOO_SMALL:
                return "user seed too small";

            case ApiError::DEVICE_NOT_FOUND:
                return "device not found";
            case ApiError::DEVICE_NOT_AUTHORIZED:
                return "device not authorized";
            case ApiError::DEVICE_UNAUTHORIZED:
                return "device unauthorized";
            case ApiError::DEVICE_REMOVED:
                return "device removed";

            case ApiError::RESOURCE_ALREADY_EXISTS:
                return "resource already exists";
            case ApiError::RESOURCE_NOT_FOUND:
                return "resource not found";

            case ApiError::AUTHORIZATION_CODE_INVALID:
                return "authorization code invalid";

            case ApiError::ACTION_NOT_SUPPORTED:
                return "action not supported";
            case ApiError::ACTION_PROHIBITED:
                return "action prohibited";

            case ApiError::SERVER_ERROR:
                return "unknown server error occurred";
            case ApiError::NODE_NOT_FOUND:
                return "url is not available";
            case ApiError::METHOD_NOT_ALLOWED:
                return "method is not allowed";

            default:
                return "unknown api error occurred with code " . $apiError;
        }
    }
}
