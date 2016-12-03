<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 03/12/2016
 * Time: 20:56
 */

namespace Famoser\SyncApi\Framework;


use Famoser\SyncApi\Services\Interfaces\DatabaseServiceInterface;
use Famoser\SyncApi\Services\Interfaces\LoggingServiceInterface;
use Famoser\SyncApi\Services\Interfaces\RequestServiceInterface;
use Famoser\SyncApi\SyncApiApp;
use Interop\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

/**
 * resolves the classes distributed by the ContainerInterface
 *
 * @package Famoser\SyncApi\Framework
 */
class ContainerBase
{
    /* @var ContainerInterface $container */
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
     * return the logging service
     *
     * @return LoggingServiceInterface
     */
    protected function getLoggingService()
    {
        return $this->container->get(SyncApiApp::LOGGING_SERVICE_KEY);
    }

    /**
     * return the logger
     *
     * @return LoggingServiceInterface
     */
    protected function getSettingsArray()
    {
        return $this->container->get(SyncApiApp::SETTINGS_KEY);
    }

    /**
     * get database helper, used for database access
     *
     * @return DatabaseServiceInterface
     */
    protected function getDatabaseService()
    {
        return $this->container->get(SyncApiApp::DATABASE_SERVICE_KEY);
    }

    /**
     * get logger
     *
     * @return RequestServiceInterface
     */
    protected function getRequestService()
    {
        return $this->container->get(SyncApiApp::REQUEST_SERVICE_KEY);
    }

    /**
     * get router
     *
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->container->get("router");
    }

    /**
     * get the view
     *
     * @return mixed
     */
    protected function getView()
    {
        return $this->container->get("view");
    }
}