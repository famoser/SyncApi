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
    const None = 0;
    const Create = 1;
    const Read = 2;
    const Update = 3;
    const Delete = 4;
    const ConfirmVersion = 5;
    const ConfirmAccess = 6;
}
