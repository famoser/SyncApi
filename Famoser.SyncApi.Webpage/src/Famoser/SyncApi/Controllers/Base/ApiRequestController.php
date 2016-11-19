<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:25
 */

namespace Famoser\SyncApi\Controllers\Base;

use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Types\ApiError;
use Slim\Http\Response;

/**
 * base class for all api requests
 * 
 * Class ApiRequestController
 * @package Famoser\SyncApi\Controllers\Base
 */
class ApiRequestController extends BaseController
{
    /* @var Application $application */
    private $application;

    /**
     * @param $applicationId
     * @return Application
     * @throws ApiException
     */
    protected function getApplication($applicationId)
    {
        if ($this->application != null) {
            return $this->application;
        }

        $this->application = $this->getDatabaseHelper()->getSingleFromDatabase(
            new Application(),
            "application_id = :application_id",
            ["application_id" => $applicationId]
        );

        if ($this->application == null) {
            throw new ApiException(ApiError::APPLICATION_NOT_FOUND);
        }

        return $this->application;
    }

    /**
     * checks if request is valid: checks authentication code & existence of user & application
     *
     * @param  BaseRequest $req
     * @return bool
     * @throws ApiException
     */
    protected function authorizeRequest(BaseRequest $req)
    {
        $application = $this->getApplication($req->ApplicationId);
        $user = $this->getUser($req);

        if ($this->getRequestService()->validateAuthCode(
            $req->AuthorizationCode,
            $application->application_seed,
            $user->personal_seed
        )
        ) {
            throw new ApiException(ApiError::AUTHORIZATION_CODE_INVALID);
        }
        return true;
    }


    /* @var User $user */
    private $user;

    /**
     * tries to get the current user. throws exception if not found or user removed!
     *
     * @param BaseRequest $req
     * @return User
     * @throws ApiException
     */
    protected function getUser(BaseRequest $req)
    {
        if ($this->user != null) {
            return $this->user;
        }

        $this->user = $this->tryGetUser($req);
        if ($this->user == null) {
            throw new ApiException(ApiError::USER_NOT_FOUND);
        }
        if ($this->user->is_deleted) {
            throw new ApiException(ApiError::USER_REMOVED);
        }
        return $this->user;
    }

    /**
     * tries to get the user. does not fail if not found!
     *
     * @param BaseRequest $req
     * @return User
     */
    protected function tryGetUser(BaseRequest $req)
    {
        return $this->getDatabaseHelper()->getSingleFromDatabase(
            new User(),
            "guid = :guid AND application_id = :application_id",
            ["guid" => $req->UserId, "application_id" => $req->ApplicationId]
        );
    }

    /* @var Device $device */
    private $device;

    /**
     * @param BaseRequest $req
     * @return Device
     * @throws ApiException
     */
    protected function getDevice(BaseRequest $req)
    {
        if ($this->device != null) {
            return $this->device;
        }

        $this->device = $this->tryGetDevice($req);
        if ($this->device == null) {
            throw new ApiException(ApiError::DEVICE_NOT_FOUND);
        }
        if ($this->device->is_deleted) {
            throw new ApiException(ApiError::DEVICE_REMOVED);
        }
        return $this->device;
    }

    /**
     * @param BaseRequest $req
     * @return Device
     * @throws ApiException
     */
    protected function tryGetDevice(BaseRequest $req)
    {
        return $this->getDatabaseHelper()->getSingleFromDatabase(
            new Device(),
            "guid = :guid AND user_guid = :user_guid AND is_deleted = :is_deleted",
            ["guid" => $req->DeviceId, "user_guid" => $this->getUser($req)->guid]
        );
    }

    /* @var string[] $collectionIds */
    private $collectionIds;

    /**
     * @param BaseRequest $req
     * @return array
     * @throws ApiException
     */
    protected function getCollectionIds(BaseRequest $req)
    {
        if ($this->collectionIds != null) {
            return $this->collectionIds;
        }

        $userCollectionIds = $this->getDatabaseHelper()->getFromDatabase(
            new UserCollection(),
            "user_guid =:user_guid",
            ["user_guid" => $this->getUser($req)->guid],
            null,
            1000,
            "collection_guid"
        );

        $this->collectionIds = [];
        foreach ($userCollectionIds as $co) {
            $this->collectionIds[] = $co->collection_guid;
        }

        return $this->collectionIds;
    }


    /**
     * checks if request is authenticated: checks if device is authenticated
     *
     * @param  BaseRequest $req
     * @return bool
     * @throws ApiException
     */
    protected function authenticateRequest(BaseRequest $req)
    {
        $device = $this->getDevice($req);
        if (!$device->is_authenticated) {
            throw new ApiException(ApiError::DEVICE_NOT_AUTHORIZED);
        }
        return $device->is_authenticated;
    }

    /**
     * returns model as json
     *
     * @param  Response $response
     * @param  $model
     * @return Response
     */
    protected function returnJson(Response $response, BaseResponse $model)
    {
        $response->getBody()->write(json_encode($model));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
