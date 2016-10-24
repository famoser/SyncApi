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
}