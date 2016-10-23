<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 10:19
 */

namespace Famoser\SyncApi\Middleware;


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\Uri;


class AuthorizationMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $uri = $request->getUri();
        if ($uri->getPath() != "/authorization")
        {
            //check if access granted
            
        }

        $response = $next($request, $response);
        return $response;
    }
}