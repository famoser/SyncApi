<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/05/2016
 * Time: 21:52
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Types\ApiError;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;

class ApiVersionMiddleware extends BaseMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        if (strpos($request->getRequestTarget(), "/1.0") === 0) {
            $newpath = str_replace("/1.0", "",$request->getRequestTarget());
            $response = $next($request->withRequestTarget($newpath)->withAttribute("api_version", 1), $response);
        } else {
            $response->withStatus(406, "API version not supported");
            $resp = new ApiResponse(false, ApiError::ApiVersionInvalid);
            $response->withJson($resp);
        }
        return $response;
    }
}