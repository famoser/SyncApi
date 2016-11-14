<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 15:08
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Services\Interfaces\LoggerInterface;
use Interop\Container\ContainerInterface;

class BaseMiddleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * get logger
     * 
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->container["logger"];
    }
}
