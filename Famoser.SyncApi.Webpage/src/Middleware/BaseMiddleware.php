<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 15:08
 */

namespace Famoser\SyncApi\Middleware;


use Interop\Container\ContainerInterface;

class BaseMiddleware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
