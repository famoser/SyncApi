<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/06/2016
 * Time: 13:22
 */

namespace Famoser\SyncApi\Helpers;


class GuidHelper
{
    public static function createGuid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public static function isGuidValid($guid)
    {
        return $guid != "" && $guid != "00000000-0000-0000-0000-000000000000";
    }
}
