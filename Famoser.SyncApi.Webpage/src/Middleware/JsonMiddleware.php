<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:03
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Helpers\LogHelper;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Types\ApiError;
use Slim\Http\Request;
use Slim\Http\Response;

class JsonMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        //check if POST requests contain json
        if ($request->getMethod() == "POST") {
            $jsonObj = $request->getParsedBody();
            if ($jsonObj == null) {
                $response->withStatus(400, "No json content in POST request");
                LogHelper::log($request->getBody(), "JsonMiddleware.txt");
                $resp = new ApiResponse(false, ApiError::RequestJsonFailure);
                $response->withJson($resp);
            } else {
                LogHelper::log(json_encode($request->getParsedBody(), JSON_PRETTY_PRINT), "JsonMiddleware.txt");
            }
        }
        $response = $next($request, $response);
        return $response;
    }
}