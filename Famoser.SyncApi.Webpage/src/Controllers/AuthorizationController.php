<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/05/2016
 * Time: 21:33
 */

namespace Famoser\SyncApi\Controllers;


use Exception;
use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
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

class AuthorizationController extends ApiRequestController
{
    /**
     * generates easily readable random string
     * @param int $length
     * @return string
     */
    private function generateReadableRandomString($length = 6)
    {
        $consonants = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");
        $vocals = array("a", "e", "i", "o", "u");
        $random = "";
        srand((double)microtime() * 1000000);
        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $random .= $consonants[rand(0, 19)];
            $random .= $vocals[rand(0, 4)];
        }
        return $random;
    }

    /**
     * Use an authentication code to authenticate an existing device.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ApiException|ServerException
     */
    public function useCode(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseAuthorizationRequest($request);
        $this->authorizeRequest($req);

        //clean up old ones
        $settingRepo = $this->getSettingRepository($req->ApplicationId);
        $expireTime = $settingRepo->getAuthorizationCodeValidTime();
        $this->getDatabaseHelper()->execute("DELETE FROM authorization_codes WHERE valid_till_date_time < :valid_till_date_time", array("valid_till_date_time" => time() - $expireTime));

        //try to get new one
        $authCode = $this->getDatabaseHelper()->getSingleFromDatabase(new AuthorizationCode(), "code = :code AND user_guid = :user_guid", array("code" => $req->ClientMessage, "user_guid" => $req->UserId));
        if ($authCode == null) {
            throw new ApiException(ApiError::AuthorizationCodeInvalid);
        }

        //try to get existing device
        $device = $this->getDevice($req);
        if ($device == null) {
            throw new ApiException(ApiError::DeviceNotFound);
        }

        //delete auth code
        if (!$this->getDatabaseHelper()->deleteFromDatabase($authCode))
            throw new ServerException(ServerError::DatabaseSaveFailure);

        //authenticate device
        $device->is_authenticated = true;
        if (!$this->getDatabaseHelper()->saveToDatabase($device))
            throw new ServerException(ServerError::DatabaseSaveFailure);

        //return successful notice
        return ResponseHelper::getJsonResponse($response, new AuthorizationResponse());
    }

    /**
     * Generate an authentication code for the user. Device must be authenticated to do this.
     * Return the authentication code in the server message
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws ApiException
     * @throws ServerException
     */
    public function generate(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseAuthorizationRequest($request);
        $this->authorizeRequest($req);

        //get device
        $device = $this->getDevice($req);
        if (!$device->is_authenticated) {
            throw new ApiException(ApiError::DeviceNotAuthorized);
        }

        //get settings repo
        $settingRepo = $this->getSettingRepository($req->ApplicationId);

        //create auth code
        $authCode = new AuthorizationCode();
        $authCode->user_guid = $req->UserId;
        $authCode->code = $this->generateReadableRandomString($settingRepo->getAuthorizationCodeLength());
        $authCode->valid_till_date_time = time() + $settingRepo->getAuthorizationCodeValidTime();
        if (!$this->getDatabaseHelper()->saveToDatabase($authCode))
            throw new ServerException(ServerError::DatabaseSaveFailure);

        //return auth code to device
        $resp = new AuthorizationResponse();
        $resp->ServerMessage = $authCode->code;
        return ResponseHelper::getJsonResponse($response, $resp);
    }

    /**
     * syncs user & device objects.
     * @param Request $request
     * @param Response $response
     * @param $args
     * @throws Exception
     */
    public function sync(Request $request, Response $response, $args)
    {

        /*
         *
            $device->identifier = $req->DeviceEntity->Identifier;

        $entityVersion = new ContentVersion();
        $entityVersion->content = $req->DeviceEntity->Content;
        $entityVersion->content_type = ContentType::Device;
        $entityVersion->create_date_time = time();
        $entityVersion->entity_guid = $req->DeviceEntity->Id;
        $entityVersion->version_guid = $req->DeviceEntity->VersionId;
        if (!$this->getDatabaseHelper()->saveToDatabase($entityVersion))
            return $this->returnServerError($response, "database error");

         */
        throw new \Exception("not implemented");
    }
}