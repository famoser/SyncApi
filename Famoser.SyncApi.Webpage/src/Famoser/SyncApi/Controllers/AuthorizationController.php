<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 24/05/2016
 * Time: 21:33
 */

namespace Famoser\SyncApi\Controllers;

use Exception;
use Famoser\SyncApi\Controllers\Base\ApiSyncController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Entities\AuthorizationCode;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * this controller is concerned with syncing users & devices, and generating authorization codes
 *
 * @package Famoser\SyncApi\Controllers
 */
class AuthorizationController extends ApiSyncController
{
    /**
     * generates easily readable random string
     *
     * @param  int $length
     * @return string
     */
    private function generateReadableRandomString($length = 6)
    {
        //only taking consonants which sound unique
        $consonants = [
            "b", "c", "d", "g", "h", "k", "l",
            "n", "p", "r", "s", "t", "x", "z"];
        $vocals = ["a", "e", "i", "o", "u"];
        $random = "";
        srand((double)microtime() * 1000000);
        $max = $length / 2;
        for ($i = 0; $i < $max; $i++) {
            $random .= $consonants[rand(0, count($consonants) - 1)];
            $random .= $vocals[rand(0, count($vocals) - 1)];
        }
        return $random;
    }

    /**
     * ensures personal seed is valid. checks if it is:
     * - not missing
     * - numeric
     * - bigger than 1000
     *
     * @param  $personalSeed
     * @throws ApiException
     */
    private function ensureValidPersonalSeed($personalSeed)
    {
        if ($personalSeed == "") {
            throw new ApiException(ApiError::PERSONAL_SEED_MISSING);
        }
        if (!is_numeric($personalSeed)) {
            throw new ApiException(ApiError::PERSONAL_SEED_NOT_NUMERIC);
        }
        if ($personalSeed < 1000) {
            throw new ApiException(ApiError::PERSONAL_SEED_TOO_SMALL);
        }
    }

    /**
     * Use an authentication code to authenticate an existing device.
     *
     * @param  Request $request
     * @param  Response $response
     * @param  $args
     * @return Response
     * @throws ApiException|ServerException
     */
    public function useCode(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseAuthorizationRequest($request);
        $this->authorizeRequest($req);

        //clean up expired auth codes
        $this->getDatabaseService()->execute(
            "DELETE FROM authorization_codes WHERE valid_till_date_time < :valid_till_date_time",
            ["valid_till_date_time" => time()]);

        //try to get auth code
        $authCode = $this->getDatabaseService()->getSingleFromDatabase(
            new AuthorizationCode(),
            "code = :code AND user_guid = :user_guid",
            ["code" => $req->ClientMessage, "user_guid" => $req->UserId]
        );

        if ($authCode == null) {
            throw new ApiException(ApiError::AUTHORIZATION_CODE_INVALID);
        }

        //try to get existing device
        $device = $this->getDevice($req);
        if ($device == null) {
            throw new ApiException(ApiError::DEVICE_NOT_FOUND);
        }

        //delete auth code
        if (!$this->getDatabaseService()->deleteFromDatabase($authCode)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }

        //authenticate device
        $device->is_authenticated = true;
        if (!$this->getDatabaseService()->saveToDatabase($device)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }

        //return successful notice
        return $this->returnJson($response, new AuthorizationResponse());
    }

    /**
     * Generate an authentication code for the user. Device must be authenticated to do this.
     * Return the authentication code in the server message
     *
     * @param  Request $request
     * @param  Response $response
     * @param  $args
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws ApiException
     * @throws ServerException
     */
    public function generate(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseAuthorizationRequest($request);
        $this->authorizeRequest($req);

        //get device
        $device = $this->getDevice($req);
        if (!$device->is_authenticated) {
            throw new ApiException(ApiError::DEVICE_NOT_AUTHORIZED);
        }

        //get settings repo
        $settingRepo = $this->getSettingRepository($req->ApplicationId);

        //create auth code
        $authCode = new AuthorizationCode();
        $authCode->user_guid = $req->UserId;
        $authCode->code = $this->generateReadableRandomString($settingRepo->getAuthorizationCodeLength());
        $authCode->valid_till_date_time = time() + (int)$settingRepo->getAuthorizationCodeValidTime();
        if (!$this->getDatabaseService()->saveToDatabase($authCode)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }

        //return auth code to device
        $resp = new AuthorizationResponse();
        $resp->ServerMessage = $authCode->code;
        return $this->returnJson($response, $resp);
    }

    /**
     * syncs user & device objects.
     *
     * @param  Request $request
     * @param  Response $response
     * @param  $args
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function sync(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseAuthorizationRequest($request);

        if ($req->UserEntity == null || $req->UserEntity->OnlineAction != OnlineAction::CREATE) {
            $this->authorizeRequest($req);
            if ($req->DeviceEntity->OnlineAction != OnlineAction::CREATE) {
                $this->authenticateRequest($req);
            } // else: create device, so no authorization required
        } //else: create user, so no authorization required

        $resp = new AuthorizationResponse();

        //sync user
        if ($req->UserEntity != null) {
            $res = $this->syncInternal(
                $req,
                [$req->UserEntity],
                ContentType::USER
            );

            if (count($res) > 0) {
                $resp->UserEntity = $res;
            }
        }

        //sync device
        if ($req->DeviceEntity != null) {
            $res = $this->syncInternal(
                $req,
                [$req->DeviceEntity],
                ContentType::DEVICE
            );
            if (count($res) > 0) {
                $resp->DeviceEntity = $res;
            }
        }
        $resp = new AuthorizationResponse();

        return $this->returnJson($response, $resp);
    }

    /**
     * get all entities the user has access to
     *
     * @param BaseRequest $req
     * @param $contentType
     * @return \Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity[]
     * @throws ServerException
     */
    protected function getAll(BaseRequest $req, $contentType)
    {
        if ($contentType == ContentType::USER) {
            //get all accessible users (which is obv. only one)
            $user = $this->tryGetUser($req);
            if ($user != null) {
                return [$user];
            }
            return [];
        } else if ($contentType == ContentType::DEVICE) {
            $device = $this->tryGetDevice($req);
            if ($device != null) {
                return [$device];
            }
            return [];
        } else {
            throw new ServerException(ServerError::FORBIDDEN);
        }
    }

    /**
     * create a new entity ready to insert into database
     *
     * @param BaseRequest $req
     * @param $contentType
     * @param BaseCommunicationEntity $commEntity
     * @return BaseSyncEntity
     * @throws ApiException
     * @throws ServerException
     */
    protected function createEntity(BaseRequest $req, $contentType, BaseCommunicationEntity $commEntity)
    {
        if (!$req instanceof AuthorizationRequest) {
            throw new ServerException(ServerError::FORBIDDEN);
        }
        if ($contentType == ContentType::USER) {
            $this->ensureValidPersonalSeed($req->UserEntity->PersonalSeed);

            $user = new User();
            $user->application_id = $req->ApplicationId;
            $user->personal_seed = $req->UserEntity->PersonalSeed;

            return $user;
        } elseif ($contentType == ContentType::DEVICE) {
            $devices = $this->getDatabaseService()->countFromDatabase(
                new Device(),
                "user_guid = :user_guid AND is_deleted = :is_deleted",
                ["user_guid" => $this->getUser($req)->guid, "is_deleted" => false]
            );

            $device = new Device();
            $device->user_guid = $this->getUser($req)->guid;
            $device->is_authenticated = $devices == 0;

            return $device;
        }
        throw new ServerException(ServerError::FORBIDDEN);
    }
}
