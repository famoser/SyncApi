<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:03
 */

namespace Famoser\SyncApi\Controllers;

use Famoser\SyncApi\Controllers\Base\ApiSyncController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

class CollectionController extends ApiSyncController
{
    /**
     * sync the collections
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ApiException
     */
    public function sync(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseCollectionEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $resp = new CollectionEntityResponse();
        $resp->CollectionEntities = $this->syncInternal(
            $req,
            $req->CollectionEntities,
            ContentType::COLLECTION
        );;

        return $this->returnJson($response, $resp);
    }

    /**
     * get all entities the user has access to
     *
     * @param BaseRequest $req
     * @param $contentType
     * @return \Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity[]
     * @throws ApiException
     * @throws ServerException
     */
    protected function getAll(BaseRequest $req, $contentType)
    {
        if ($contentType != ContentType::COLLECTION) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        //get all accessible collection guids
        $collectionIds = $this->getCollectionIds($req);

        //get all collections
        return $this->getDatabaseHelper()->getFromDatabase(
            new Collection(),
            "guid IN (:" . implode(',:', array_keys($collectionIds)) . ")",
            $collectionIds);
    }

    /**
     * create a new entity ready to insert into database
     *
     * @param BaseRequest $req
     * @param $contentType
     * @param BaseCommunicationEntity $communicationEntity
     * @return BaseSyncEntity
     * @throws ApiException
     * @throws ServerException
     */
    protected function createEntity(BaseRequest $req, $contentType, BaseCommunicationEntity $communicationEntity)
    {
        if ($contentType != ContentType::COLLECTION) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        $coll = new Collection();
        $coll->user_guid = $this->getUser($req)->guid;
        $coll->device_guid = $this->getDevice($req)->guid;
        return $coll;
    }
}
