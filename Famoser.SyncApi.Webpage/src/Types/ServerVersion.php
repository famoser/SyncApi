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
    const NONE = 0;
    const SAME = 1;
    const OLDER = 2;
    const NEWER = 3;
}