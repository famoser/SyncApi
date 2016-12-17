<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 03/12/2016
 * Time: 20:04
 */

namespace Famoser\SyncApi\Services\Base;


use Famoser\SyncApi\Framework\ContainerBase;

/**
 * Class BaseService: to be extended by all services
 *
 * @package Famoser\SyncApi\Services\Base
 */
class BaseService extends ContainerBase
{
    /**
     * return the modulo used in the application
     *
     * @return string
     */
    protected function getModulo()
    {
        return $this->getSettingsArray()['api_modulo'];
    }

    /**
     * return the base path for the log files
     *
     * @return string
     */
    protected function getLoggingBasePath()
    {
        return $this->getSettingsArray()['log_path'];
    }
}