<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Controllers\Base\ApiSyncController;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

class DeviceController extends ApiSyncController
{
    public function get(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseCollectionEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $resp = new CollectionEntityResponse();
        $resp->CollectionEntities = $this->syncInternal(
            $req,
            $req->CollectionEntities,
            ContentType::DEVICE
        );

        return $this->returnJson($response, $resp);
    }

    public function auth(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }

    public function unAuth(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
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
        if ($contentType != ContentType::DEVICE) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        return $this->getDatabaseHelper()->getFromDatabase(
            new Device(),
            "user_guid = :user_guid",
            array("user_guid" => $this->getUser($req)->guid)
        );
    }

    /**
     * create a new entity ready to insert into database
     *
     * @param BaseRequest $req
     * @param $contentType
     * @return BaseSyncEntity
     * @throws ServerException
     */
    protected function createEntity(BaseRequest $req, $contentType)
    {
        if ($contentType != ContentType::DEVICE) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        $entity = new Device();
        $entity->is_authenticated = false;
        $entity->user_guid = $this->getUser($req)->guid;
        return $entity;
    }
}
