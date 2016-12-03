<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 15:08
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Services\Interfaces\LoggingServiceInterface;
use Interop\Container\ContainerInterface;

/**
 * a middleware to be overridden which abstracts the fuzzy controller handling
 * @package Famoser\SyncApi\Middleware
 */
class BaseMiddleware
{
    private $container;

    /**
     * BaseMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * get logger
     * 
     * @return LoggingServiceInterface
     */
    protected function getLogger()
    {
        return $this->container["logger"];
    }
}
