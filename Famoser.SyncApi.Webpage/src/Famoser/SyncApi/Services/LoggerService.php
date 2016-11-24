<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14.11.2016
 * Time: 12:42
 */

namespace Famoser\SyncApi\Services;

use Famoser\SyncApi\Services\Interfaces\LoggerInterface;

/**
 * the logger service is concerned to save errors which occurred while the application is running
 * @package Famoser\SyncApi\Services
 */
class LoggerService implements LoggerInterface
{
    /* @var string $basePath */
    private $basePath;

    /**
     * LoggerService constructor.
     * @param string $basePath the base path to use for all log files
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * log your message
     *
     * @param $message
     * @param $filename
     * @param bool $clearOld
     */
    public function log($message, $filename, $clearOld = true)
    {
        $path = $this->basePath . "/" . $filename;
        if ($clearOld && file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, $message, FILE_APPEND);
    }

    /**
     * get path where the log files are saved
     *
     * @return string
     */
    public function getLogPath()
    {
        return $this->basePath;
    }
}