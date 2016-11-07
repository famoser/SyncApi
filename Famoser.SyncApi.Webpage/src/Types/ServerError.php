<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24.10.2016
 * Time: 10:44
 */

namespace Famoser\SyncApi\Types;


class ServerError
{
//[Description("Json request could not be deserialized")]
    const RequestJsonFailure = 101;
//[Description("You tried to access a resource you have no access to")]
    const Forbidden = 104;
//[Description("Some required properties were missing")]
    const NotWellDefined = 105;

//[Description("Json request could not be deserialized")]
    const DatabaseSaveFailure = 201;

    public static function toString($serverError)
    {
        switch ($serverError) {
            case ServerError::RequestJsonFailure:
                return "json request could not be processed";
            case ServerError::Forbidden:
                return "you are not allowed to view this resource";
            case ServerError::NotWellDefined:
                return "the request is not well defined";
            case ServerError::DatabaseSaveFailure:
                return "changes could not be written to database";
            default:
                return "unknown server error occurred with code " . $serverError;
        }
    }
}