<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/11/2016
 * Time: 12:29
 */

namespace Famoser\SyncApi\Types;


/**
 * used to distinguish frontend errors
 *
 * @package Famoser\SyncApi\Types
 */
class FrontendError
{
    const NOT_LOGGED_IN = 0;
    const ACCESS_DENIED = 1;

    /**
     * convert to string
     *
     * @param string $code
     * @return string
     */
    public static function toString($code)
    {
        switch ($code) {
            case self::NOT_LOGGED_IN:
                return 'not logged in';
            case self::ACCESS_DENIED:
                return 'you are not allowed to view this resource';
            default:
                return 'unknown error occurred with code ' . $code;
        }
    }
}