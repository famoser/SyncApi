<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:03
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\AbstractApiSyncController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\SyncCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\HistoryEntityResponse;
use Famoser\SyncApi\Models\Communication\Response\SyncEntityResponse;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Entity;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * api controllers which takes care of the entities
 * @package Famoser\SyncApi\Controllers
 */
class EntityControllerAbstract extends AbstractApiSyncController
{
    /**
     * sync entities, return missing & updated entities
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ApiException
     */
    public function sync(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseSyncEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $resp = new SyncEntityResponse();
        $resp->SyncEntities = $this->syncInternal(
            $req,
            $req->SyncEntities,
            ContentType::ENTITY
        );

        return $this->returnJson($response, $resp);
    }

    /**
     * retrieve history entries for entity
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ApiException
     */
    public function historySync(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseHistoryEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        //get entity (checks if user has access)
        $collectionIds = $this->getCollectionIds($req);
        if (count($collectionIds) == 0) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $arr = $this->convertToStringArrayKeys($collectionIds);
        $inner = implode(',:', array_keys($arr));
        $arr['guid'] = $req->Id;
        $entity = $this->getDatabaseService()->getSingleFromDatabase(
            new Entity(),
            'guid = :guid AND collection_guid IN (:' . $inner . ')',
            $arr
        );

        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        //get missing versions from database
        /* @var ContentVersion[] $newOnes */
        if ($req->VersionIds == null || count($req->VersionIds) == 0) {
            $arr = [];
            $arr['entity_guid'] = $entity->guid;
            /* @var ContentVersion[] $newOnes */
            $newOnes = $this->getDatabaseService()->getFromDatabase(
                new ContentVersion(),
                'entity_guid = :entity_guid',
                $arr,
                'create_date_time'
            );
        } else {
            $arr = $this->convertToStringArrayKeys($req->VersionIds);
            $inner = implode(',:', array_keys($arr));
            $arr['entity_guid'] = $entity->guid;
            $newOnes = $this->getDatabaseService()->getFromDatabase(
                new ContentVersion(),
                'entity_guid = :entity_guid AND version_guid NOT IN (:' . $inner . ')',
                $arr,
                'create_date_time'
            );
        }

        //convert to entities
        $resp = new HistoryEntityResponse();
        foreach ($newOnes as $newOne) {
            $resp->CollectionEntities[] = $entity->createCommunicationEntity($newOne, OnlineAction::CREATE);
        }

        return $this->returnJson($response, $resp);
    }

    private function convertToStringArrayKeys($arr)
    {
        $str = 'rawrrr';
        $counter = 1;
        $result = [];
        foreach ($arr as $item) {
            $result[$str . $counter++] = $item;
        }
        return $result;
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
        if ($contentType != ContentType::ENTITY) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        //get all accessible collection guids
        $collectionIds = $this->getCollectionIds($req);

        //return empty array if no ids yet
        if (count($collectionIds) == 0) {
            return [];
        }

        //get all collections
        return $this->getDatabaseService()->getFromDatabase(
            new Entity(),
            'collection_guid IN (:' . implode(',:', array_keys($collectionIds)) . ')',
            $collectionIds);
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
        if (!$commEntity instanceof SyncCommunicationEntity) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        if ($contentType != ContentType::ENTITY) {
            throw new ServerException(ServerError::FORBIDDEN);
        }

        $entity = new Entity();
        $entity->collection_guid = $commEntity->CollectionId;
        $entity->device_guid = $this->getDevice($req)->guid;
        $entity->user_guid = $this->getUser($req)->guid;
        return $entity;
    }
}
