<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 29/05/2016
 * Time: 11:31
 */

namespace Famoser\SyncApi\Helpers;


class FormatHelper
{
    public static function toCSharpDateTime($input)
    {
        //returns ISO 8601 date of form 2004-02-12T15:19:21+00:00
        return date("c", $input);
    }
}