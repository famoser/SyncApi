<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:23
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Helpers\LogHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Request\Base\ApiRequest;
use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Types\ApiErrorTypes;
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


    protected function returnApiError($apiErrorType, Response $response, $debugMessage = null)
    {
        $apiError = array(
            ApiErrorTypes::DatabaseFailure => 500,
            ApiErrorTypes::ApiVersionInvalid => 406,
            ApiErrorTypes::AuthorizationCodeInvalid => 401,
            ApiErrorTypes::ContentNotFound => 404,
            ApiErrorTypes::DeviceNotFound => 401,
            ApiErrorTypes::Forbidden => 401,
            ApiErrorTypes::NoDevicesFound => 500,
            ApiErrorTypes::None => 200,
            ApiErrorTypes::NotAuthorized => 401,
            ApiErrorTypes::NotWellDefined => 400,
            ApiErrorTypes::RequestJsonFailure => 400,
            ApiErrorTypes::DeviceNotFound => 401,
            ApiErrorTypes::RequestUriInvalid => 404,
            ApiErrorTypes::Unauthorized => 401
        );

        if (!in_array($apiErrorType, $apiError)) {
            $apiError[$apiErrorType] = 500;
        }

        $resp = new ApiResponse(false, $apiErrorType);
        $resp->ApiMessage = $debugMessage;

        return $response->withStatus($apiError[$apiErrorType])->withJson($resp);
    }

    protected function isAuthorized(ApiRequest $request)
    {
        $user = $this->getAuthorizedUser($request);
        if ($user == null)
            return false;
        $device = $this->getAuthorizedDevice($request);
        if ($device != null)
            return $device->has_access;
        return false;
    }

    protected function isWellDefined(ApiRequest $request, $neededProps, $neededArrays = null)
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

    private $authorizedUser;

    /**
     * @param ApiRequest $request
     * @return User
     */
    protected function getAuthorizedUser(ApiRequest $request)
    {
        if ($this->authorizedUser == null) {
            if ($request->UserId != null) {
                $helper = $this->getDatabaseHelper();
                $this->authorizedUser = $helper->getSingleFromDatabase(new User(), "user_id=:user_id", array("user_id" => $request->UserId));
            }
        }
        return $this->authorizedUser;
    }

    private $authorizedDevice;

    /**
     * @param ApiRequest $request
     * @return Device
     */
    protected function getAuthorizedDevice(ApiRequest $request)
    {
        if ($this->authorizedDevice == null) {
            if ($request->DeviceId != null) {
                $authorizedUser = $this->getAuthorizedUser($request);

                if ($authorizedUser != null) {
                    $helper = $this->getDatabaseHelper();
                    $this->authorizedDevice = $helper->getSingleFromDatabase(new Device(), "device_id=:device_id AND user_id=:user_id", array("device_id" => $request->DeviceId, "user_id" => $authorizedUser->id));

                    if ($this->authorizedDevice != null) {
                        $this->authorizedDevice->last_request_date_time = time();
                        $helper->saveToDatabase($this->authorizedDevice);
                    }
                }
            }
        }
        return $this->authorizedDevice;
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