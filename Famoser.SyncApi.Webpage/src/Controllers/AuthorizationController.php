<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/05/2016
 * Time: 21:33
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Helpers\FormatHelper;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthorizationController extends BaseController
{
    public function useCode(Request $request, Response $response, $args)
    {
        /* template     
        $model = RequestHelper::parseAuthorizationRequest($request);
        if (!$this->isWellDefined($model, array("UserName", "DeviceName")))
            return $this->returnApiError(ServerError::NotWellDefined, $response);

        $helper = $this->getDatabaseHelper();

        $resp = new AuthorizationResponse();
        $resp->ServerMessage = "welcome aboard!";

        return ResponseHelper::getJsonResponse($response, $resp);

        */
        throw new \Exception("not implemented");

    }

    public function generate(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }

    public function sync(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }
}