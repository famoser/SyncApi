<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:25
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Response;

class ApiRequestController extends BaseController
{
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

        $dh = $this->getDatabaseHelper();
        $this->application = $dh->getSingleFromDatabase(new Application(), "application_id = :application_id AND is_deleted =:is_deleted",
            array("application_id" => $applicationId, "is_deleted" => false));
        if ($this->application == null)
            throw new ApiException(ApiError::APPLICATION_NOT_FOUND);

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

        if (RequestHelper::validateAuthCode($req->AuthorizationCode, $application->application_seed, $user->personal_seed))
            throw new ApiException(ApiError::AUTHORIZATION_CODE_INVALID);
        return true;
    }


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
            new User(), "guid = :guid AND application_id = :application_id",
            array("guid" => $req->UserId, "application_id" => $req->ApplicationId)
        );
    }

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
            throw new ApiException(ApiError::DeviceRemoved);
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
            new Device(), "guid = :guid AND user_guid = :user_guid AND is_deleted = :is_deleted",
            array("guid" => $req->DeviceId, "user_guid" => $this->getUser($req)->guid, "is_deleted" => false)
        );
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
        if (!$device->is_authenticated)
            throw new ApiException(ApiError::DEVICE_NOT_AUTHORIZED);
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

    protected function updateSyncEntity(BaseSyncEntity $entity, BaseCommunicationEntity $syncEntity)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        //un-delete if already deleted
        if ($entity->is_deleted) {
            $entity->is_deleted = false;
            if (!$this->getDatabaseHelper()->saveToDatabase($entity)) {
                throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
            }
        }

        $content = $entity->createContentVersion($syncEntity);
        if (!$this->getDatabaseHelper()->saveToDatabase($content)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }
    }

    protected function deleteSyncEntity(BaseSyncEntity $entity)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $entity->is_deleted = true;
        if (!$this->getDatabaseHelper()->saveToDatabase($entity)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }
    }
}
