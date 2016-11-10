<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/11/2016
 * Time: 18:13
 */

namespace Famoser\SyncApi\Types;


class OnlineAction
{
    const NONE = 0;
    const CREATE = 1;
    const READ = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const CONFIRM_VERSION = 5;
    const CONFIRM_ACCESS = 6;
}
