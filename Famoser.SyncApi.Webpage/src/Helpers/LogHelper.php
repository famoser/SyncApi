<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2016
 * Time: 21:46
 */

namespace Famoser\SyncApi\Helpers;


class LogHelper
{
    private static $basePath;

    public static function configure($basePath)
    {
        LogHelper::$basePath = $basePath;
    }

    public static function log($message, $filename, $clearOld = true)
    {
        $path = LogHelper::$basePath . "/" . $filename;
        if ($clearOld && file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $message, FILE_APPEND);
    }
}