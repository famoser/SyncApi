<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24.10.2016
 * Time: 10:44
 */

namespace Famoser\SyncApi\Types;


/**
 * used to distinguish server errors
 *
 * @package Famoser\SyncApi\Types
 */
class ServerError
{
    const REQUEST_JSON_FAILURE = 101;
    const FORBIDDEN = 104;
    const NOT_WELL_DEFINED = 105;
    const DATABASE_SAVE_FAILURE = 201;

    /**
     * convert to string
     * 
     * @param $serverError
     * @return string
     */
    public static function toString($serverError)
    {
        switch ($serverError) {
            case ServerError::REQUEST_JSON_FAILURE:
                return "json request could not be processed";
            case ServerError::FORBIDDEN:
                return "you are not allowed to view this resource";
            case ServerError::NOT_WELL_DEFINED:
                return "the request is not well defined";
            case ServerError::DATABASE_SAVE_FAILURE:
                return "changes could not be written to database";
            default:
                return "unknown server error occurred with code " . $serverError;
        }
    }
}
