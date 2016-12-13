<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 15:47
 */

namespace Famoser\SyncApi\Services\Interfaces;


/**
 * a key-value storage for session variables
 *
 * @package Famoser\SyncApi\Services\Interfaces
 */
interface SessionServiceInterface
{
    /**
     * get the value from the sassion storage, returns the default if not set
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getValue($key, $default);

    /**
     * sets the value in the session storage
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setValue($key, $value);
}