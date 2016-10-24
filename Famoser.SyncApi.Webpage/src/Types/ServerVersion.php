<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 29/05/2016
 * Time: 19:28
 */

namespace Famoser\SyncApi\Types;


class ServerVersion
{
//[Description("no version on server")]
    const None = 0;
//[Description("same version on server")]
    const Same = 1;
//[Description("older version on server")]
    const Older = 2;
//[Description("newer version on server")]
    const Newer = 3;
}