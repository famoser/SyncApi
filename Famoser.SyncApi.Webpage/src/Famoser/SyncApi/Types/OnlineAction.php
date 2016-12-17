<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/11/2016
 * Time: 18:13
 */

namespace Famoser\SyncApi\Types;


/**
 * the action to be executed by the api on a resource
 *
 * @package Famoser\SyncApi\Types
 */
class OnlineAction
{
    const NONE = 0;
    const CREATE = 1;
    const READ = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const CONFIRM_VERSION = 5;
    const CONFIRM_ACCESS = 6;

    const ACCESS_GRANTED = 10;
    const ACCESS_DENIED = 11;

    const ALL_SYNC_ACTIONS = [
        self::CONFIRM_VERSION,
        self::CONFIRM_ACCESS,
        self::CREATE,
        self::READ,
        self::UPDATE,
        self::DELETE
    ];
}
