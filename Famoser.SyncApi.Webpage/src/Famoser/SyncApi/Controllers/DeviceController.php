<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiSyncController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * the device controller can authenticate devices and return all devices from a specific user
 * @package Famoser\SyncApi\Controllers
 */
class DeviceController extends ApiSyncController
{
    /**
     * gets all devices from a specific user
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Famoser\SyncApi\Exceptions\ApiException
     */
    public function get(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseCollectionEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $resp = new CollectionEntityResponse();
        $resp->CollectionEntities = $this->syncInternal(
            $req,
            $req->CollectionEntities,
            ContentType::DEVICE,
            [OnlineAction::READ, OnlineAction::CONFIRM_VERSION]
        );

        return $this->returnJson($response, $resp);
    }

    /**
     * authenticates a device
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ServerException
     */
    public function auth(Request $request, Response $response, $args)
    {
        return $this->authInternal($request, $response, true);
    }

    /**
     * unauthenticates a device
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ServerException
     */
    public function unAuth(Request $request, Response $response, $args)
    {
        return $this->authInternal($request, $response, false);
    }

    /**
     * auth / de auth device based on the value specified in action
     *
     * @param Request $request
     * @param Response $response
     * @param boolean $action
     * @return Response
     * @throws ServerException
     * @throws \Famoser\SyncApi\Exceptions\ApiException
     */
    private function authInternal(Request $request, Response $response, $action)
    {
        $req = $this->getRequestService()->parseAuthorizationRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $dev = $this->getDatabaseService()->getSingleFromDatabase(
            new Device(),
            'user_guid = :user_guid AND guid = :guid',
            ['user_guid' => $this->getUser($req)->guid, 'guid' => $req->ClientMessage]
        );
        if ($dev == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $dev->is_authenticated = $action;
        if (!$this->getDatabaseService()->saveToDatabase($dev)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }

        return $this->returnJson($response, new AuthorizationResponse());
    }

    /**
     * get all entities the user has access to
     *
     * @param BaseRequest $req
     * @param $contentType
     * @return \Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity[]
     * @throws ServerException
     */
    protected function getAllFromDatabase(BaseRequest $req, $contentType)
    {
        if ($contentType != ContentType::DEVICE) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        return $this->getDatabaseService()->getFromDatabase(
            new Device(),
            'user_guid = :user_guid',
            ['user_guid' => $this->getUser($req)->guid]
        );
    }

    /**
     * create a new entity ready to insert into database
     *
     * @param BaseRequest $req
     * @param $contentType
     * @param BaseCommunicationEntity $commEntity
     * @return BaseSyncEntity
     * @throws ServerException
     * @throws \Famoser\SyncApi\Exceptions\ApiException
     */
    protected function createEntity(BaseRequest $req, $contentType, BaseCommunicationEntity $commEntity)
    {
        //the access here is not allowed as no OnlineAction is equal to create
        throw new ServerException(ServerError::FORBIDDEN);
        /*
        if ($contentType != ContentType::DEVICE) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        $entity = new Device();
        $entity->is_authenticated = false;
        $entity->user_guid = $this->getUser($req)->guid;
        return $entity;
        */
    }
}
