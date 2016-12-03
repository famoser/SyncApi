<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 03/12/2016
 * Time: 20:04
 */

namespace Famoser\SyncApi\Services\Base;


use Famoser\SyncApi\Services\Interfaces\LoggingServiceInterface;
use Famoser\SyncApi\SyncApiApp;
use Interop\Container\ContainerInterface;
use Slim\Container;

/**
 * Class BaseService: to be extended by all services
 *
 * @package Famoser\SyncApi\Services\Base
 */
class BaseService
{
    /* @var Container $container */
    private $container;

    /**
     * RequestService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * return the logger
     *
     * @return LoggingServiceInterface
     */
    protected function getLogger()
    {
        return $this->container->get(SyncApiApp::LOGGING_SERVICE_KEY);
    }

    /**
     * return the modulo used in the application
     *
     * @return int
     */
    protected function getModulo()
    {
        return $this->container->get(SyncApiApp::SETTINGS_KEY)["api_modulo"];
    }

    /**
     * return the base path for the log files
     *
     * @return string
     */
    protected function getLoggingBasePath()
    {
        return $this->container->get(SyncApiApp::SETTINGS_KEY)["log_path"];
    }
}