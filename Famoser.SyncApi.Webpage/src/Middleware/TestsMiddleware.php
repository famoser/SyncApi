<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/05/2016
 * Time: 21:46
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Helpers\DatabaseHelper;
use Interop\Container\ContainerInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class TestsMiddleware extends BaseMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (strpos($request->getRequestTarget(), "/tests") === 0) {
            $newpath = str_replace("/tests", "", $request->getRequestTarget());
            DatabaseHelper::setPathKey('test_path');
            return $next($request->withRequestTarget($newpath)->withAttribute("test_mode", true), $response);
        } else {
            $response = $next($request, $response);
            return $response;
        }
    }
}