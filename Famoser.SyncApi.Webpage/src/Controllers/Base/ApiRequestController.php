<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:25
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Types\ApiError;

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
        if ($this->application != null)
            return $this->application;

        $dh = $this->getDatabaseHelper();
        $this->application = $dh->getSingleFromDatabase(new Application(), "application_id = :application_id AND is_deleted =:is_deleted",
            array("application_id" => $applicationId, "is_deleted" => false));
        if ($this->application == null)
            throw new ApiException(ApiError::ApplicationNotFound);

        return $this->application;
    }

    /**
     * checks if request is valid: checks authentication code & existence of user & application
     * @param BaseRequest $req
     * @return bool
     * @throws ApiException
     */
    protected function authorizeRequest(BaseRequest $req)
    {
        $dh = $this->getDatabaseHelper();
        $application = $this->getApplication($req->ApplicationId);
        $user = $this->getUser($req);

        if (RequestHelper::validateAuthCode($req->AuthorizationCode, $application->application_seed, $user->personal_seed))
            throw new ApiException(ApiError::AuthorizationCodeInvalid);
        return true;
    }


    private $user;

    /**
     * @param BaseRequest $req
     * @return User
     * @throws ApiException
     */
    protected function getUser(BaseRequest $req)
    {
        if ($this->user != null)
            return $this->user;

        $this->user = $this->getDatabaseHelper()->getSingleFromDatabase(new User(), "guid = :guid AND application_id = :application_id AND is_deleted = :is_deleted",
            array("guid" => $req->UserId, "application_id" => $req->ApplicationId, "is_deleted" => false));
        if ($this->user == null)
            throw new ApiException(ApiError::UserNotFound);
        return $this->user;
    }

    private $device;

    /**
     * @param BaseRequest $req
     * @return Device
     * @throws ApiException
     */
    protected function getDevice(BaseRequest $req)
    {
        if ($this->device != null)
            return $this->device;

        $this->device = $this->getDatabaseHelper()->getSingleFromDatabase(new Device(), "guid = :guid AND user_guid = :user_guid AND is_deleted = :is_deleted",
            array("guid" => $req->DeviceId, "user_guid" => $this->getUser($req)->guid, "is_deleted" => false));
        if ($this->device == null)
            throw new ApiException(ApiError::DeviceNotFound);
        return $this->device;
    }

    /**
     * checks if request is authenticated: checks if device is authenticated
     * @param BaseRequest $req
     * @return bool
     * @throws ApiException
     */
    protected function authenticateRequest(BaseRequest $req)
    {
        $device = $this->getDevice($req);
        if (!$device->is_authenticated)
            throw new ApiException(ApiError::DeviceNotAuthorized);
        return $device->is_authenticated;
    }
}