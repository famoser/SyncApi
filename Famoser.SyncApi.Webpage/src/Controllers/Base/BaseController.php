<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:23
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Helpers\LogHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;
use Interop\Container\ContainerInterface;
use Slim\Http\Response;

class BaseController
{
    protected $container;

    //Constructor
    public function __construct(ContainerInterface $ci)
    {
        $this->container = $ci;
    }

    protected function returnServerError(Response $response, $debugMessage = null)
    {
        return $response->withStatus(500);
    }

    protected function returnApiError($apiErrorType, Response $response, $debugMessage = null)
    {
        $resp = new BaseResponse();
        $resp->RequestFailed = true;
        $resp->ApiError = $apiErrorType;
        return $response->withJson($resp);
    }

    protected function isWellDefined(BaseRequest $request, $neededProps, $neededArrays = null)
    {
        if ($neededProps != null)
            foreach ($neededProps as $neededProp) {
                if ($request->$neededProp == null) {
                    LogHelper::log("not a property: " . $neededProp . " in object " . json_encode($request, JSON_PRETTY_PRINT), "isWellDefined_" . uniqid() . ".txt");
                    return false;
                }
            }
        if ($neededArrays != null)
            foreach ($neededArrays as $neededArray) {
                if (!is_array($request->$neededArray)) {
                    LogHelper::log("not an array: " . $neededArray . " in object " . json_encode($request, JSON_PRETTY_PRINT), "isWellDefined_" . uniqid() . ".txt");
                    return false;
                }
            }
        return true;
    }

    private $databaseHelper;
    protected function getDatabaseHelper()
    {
        if ($this->databaseHelper == null)
            $this->databaseHelper = new DatabaseHelper($this->container);
        return $this->databaseHelper;
    }

    protected function renderTemplate(Response $response, $path, $args)
    {
        return $this->container->get("view")->render($response, $path . ".html.twig", $args);
    }
}