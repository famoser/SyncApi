<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 15:47
 */

namespace Famoser\SyncApi\Services;


use Famoser\SyncApi\Services\Interfaces\SessionServiceInterface;

/**
 * a key-value storage for session variables
 *
 * @package Famoser\SyncApi\Services
 */
class SessionService implements SessionServiceInterface
{
    const FRONTEND_USER_ID = 'frontend_user_id';

    /**
     * get the value from the sassion storage, returns the default if not set
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getValue($key, $default)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            $this->setValue($key, $default);
            return $_SESSION[$key];
        }
    }

    /**
     * sets the value in the session storage
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setValue($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}