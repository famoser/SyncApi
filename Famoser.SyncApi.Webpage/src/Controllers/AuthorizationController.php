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
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\AuthorizationCode;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

class AuthorizationController extends BaseController
{
    private $application;

    private function getApplication($applicationId)
    {
        if ($this->application != null)
            return $this->application;

        $dh = $this->getDatabaseHelper();
        $this->application = $dh->getSingleFromDatabase(new Application(), "application_id = :application_id", array("application_id" => $applicationId));
        if ($this->application == null)
            throw new Exception("application not found");

        return $this->application;
    }

    private function authorizeRequest(BaseRequest $req)
    {
        $dh = $this->getDatabaseHelper();
        $application = $this->getApplication($req->ApplicationId);
        $user = $dh->getSingleFromDatabase(new User(), "guid = :guid", array("guid" => $req->UserId));

        if (RequestHelper::validateAuthCode($req->AuthorizationCode, $application->application_seed, $user->personal_seed))
            throw new Exception("authentication code invalid");
        return true;
    }

    public function useCode(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseAuthorizationRequest($request);
        $this->authorizeRequest($req);

        //clean up
        $settingRepo = $this->getSettingRepository($req->ApplicationId);
        $expireTime = $settingRepo->getAuthorizationCodeValidTime();
        $this->getDatabaseHelper()->execute("DELETE FROM authorization_codes WHERE valid_till_date_time < :valid_till_date_time", array("valid_till_date_time" => time() - $expireTime));

        $authCode = $this->getDatabaseHelper()->getSingleFromDatabase(new AuthorizationCode(), "code = :code AND user_guid = :user_guid", array("code" => $req->ClientMessage, "user_guid" => $req->UserId));
        if ($authCode == null) {
            $resp = new AuthorizationResponse();
            $resp->RequestFailed = true;
            $resp->ApiError = ApiError::AuthorizationCodeInvalid;
            return ResponseHelper::getJsonResponse($response, $resp);
        }

        $device = new Device();
        $device->user_guid = $req->UserId;
        $device->guid = $req->DeviceId;
        $device->identifier = $req->DeviceEntity->Identifier;
        if (!$this->getDatabaseHelper()->saveToDatabase($device))
            return $this->returnServerError($response, "database error");

        $entityVersion = new ContentVersion();
        $entityVersion->content = $req->DeviceEntity->Content;
        $entityVersion->content_type = ContentType::Device;
        $entityVersion->create_date_time = time();
        $entityVersion->entity_guid = $req->DeviceEntity->Id;
        $entityVersion->version_guid = $req->DeviceEntity->VersionId;
        if (!$this->getDatabaseHelper()->saveToDatabase($entityVersion))
            return $this->returnServerError($response, "database error");

        $resp = new AuthorizationResponse();
        return ResponseHelper::getJsonResponse($response, $resp);
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