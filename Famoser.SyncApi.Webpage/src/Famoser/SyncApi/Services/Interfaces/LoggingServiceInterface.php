<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14.11.2016
 * Time: 12:42
 */

namespace Famoser\SyncApi\Services\Interfaces;


/**
 * the interface to a logger, which logs errors
 * @package Famoser\SyncApi\Services\Interfaces
 */
interface LoggingServiceInterface
{
    /**
     * log your message
     *
     * @param string $message
     * @param string $filename
     * @param bool $clearOld
     * @return void
     */
    public function log($message, $filename, $clearOld = true);

    /**
     * get path where the log files are saved
     * 
     * @return string
     */
    public function getLogPath();
}