<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/11/2016
 * Time: 12:29
 */

namespace Famoser\SyncApi\Types;


class FrontendError
{
    const NOT_LOGGED_IN = 0;

    public function toString($code)
    {
        switch ($code) {
            case FrontendError::NOT_LOGGED_IN:
                return "not logged in";
            default:
                return "unknown error occurred";
        }
    }
}