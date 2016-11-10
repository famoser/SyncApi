<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:03
 */

namespace Famoser\SyncApi\Controllers;

use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

class CollectionController extends ApiRequestController
{
    public function sync(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseCollectionEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $resp = new CollectionEntityResponse();
        $askedForGuids = [];
        foreach ($req->CollectionEntities as $collectionEntity) {
            $entity = $collectionEntity;
            $askedForGuids[] = $entity->Id;
            if ($entity->OnlineAction == OnlineAction::CREATE) {
                $coll = $this->getCollectionById($req, $entity->Id);
                if ($coll != null) {
                    //this happens if id guid is set twice. can not happen under normal circumstances
                    throw new ApiException(ApiError::RESOURCE_ALREADY_EXISTS);
                }

                $coll = new Collection();
                $coll->user_guid = $this->getUser($req)->guid;
                $coll->device_guid = $this->getDevice($req)->guid;
                $coll->guid = $entity->Id;
                $coll->identifier = $entity->Identifier;

                if (!$this->getDatabaseHelper()->saveToDatabase($coll)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }

                $content = $coll->createContentVersion($entity);
                if (!$this->getDatabaseHelper()->saveToDatabase($content)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }
            } elseif ($entity->OnlineAction == OnlineAction::UPDATE) {
                $coll = $this->getCollectionById($req, $entity->Id);
                $this->updateSyncEntity($coll, $entity);
            } elseif ($entity->OnlineAction == OnlineAction::DELETE) {
                $coll = $this->getCollectionById($req, $entity->Id);
                $this->deleteSyncEntity($coll);
            } elseif ($entity->OnlineAction == OnlineAction::READ) {
                //todo: refactor sync for less code duplication
                $coll = $this->getCollectionById($req, $entity->Id);

                if ($coll == null) {
                    throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
                }

                $ver = $this->getActiveVersion($coll);

                if ($coll == null) {
                    throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
                }

                $resp->CollectionEntities[] = $ver->createCollectionEntity($coll, OnlineAction::READ);
            } elseif ($entity->OnlineAction == OnlineAction::CONFIRM_VERSION) {
                $coll = $this->getCollectionById($req, $entity->Id);

                if ($coll == null) {
                    throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
                }

                $ver = $this->getActiveVersion($coll);

                if ($coll == null) {
                    throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
                }

                if ($coll->is_deleted) {
                    $resp->CollectionEntities[] = $ver->createCollectionEntity($coll, OnlineAction::DELETE);
                } elseif ($entity->VersionId != $ver->version_guid) {
                    $resp->CollectionEntities[] = $ver->createCollectionEntity($coll, OnlineAction::UPDATE);
                }
            } else {
                throw new ApiException(ApiError::ACTION_NOT_SUPPORTED);
            }
        }

        $collections = $this->getAllCollections($req);
        $newOnes = array_diff($askedForGuids, array_keys($collections));
        foreach ($newOnes as $newOne) {
            if (!$collections[$newOne]->is_deleted) {
                $ver = $this->getActiveVersion($collections[$newOne]);
                $resp->CollectionEntities[] = $ver->createCollectionEntity($collections[$newOne], OnlineAction::CREATE);
            }
        }

        return $this->returnJson($response, $resp);
    }

    private $tempCollections;

    /**
     * get all collections accessible by the current user
     *
     * @param  BaseRequest $req
     * @return Collection[]
     */
    private function getAllCollections(BaseRequest $req)
    {
        if ($this->tempCollections == null) {
            $this->cacheCollections($req);
        }

        return $this->tempCollections;
    }

    /**
     * get a collection by a guid accessible for the user
     *
     * @param  BaseRequest $req
     * @param  $guid
     * @return Collection
     * @throws \Famoser\SyncApi\Exceptions\ApiException
     */
    private function getCollectionById(BaseRequest $req, $guid)
    {
        if ($this->tempCollections == null) {
            $this->cacheCollections($req);
        }

        return in_array($guid, $this->tempCollections) ? $this->tempCollections[$guid] : null;
    }

    /**
     * gets all collection accessible by the current user and caches them
     *
     * @param  BaseRequest $req
     * @throws ApiException
     */
    private function cacheCollections(BaseRequest $req)
    {
        //get all accessible collection guids
        $dbh = $this->getDatabaseHelper();
        $userCollections = $dbh->getFromDatabase(
            new UserCollection(),
            "user_guid =:user_guid",
            array("user_guid" => $this->getUser($req)->guid),
            null,
            1000,
            "collection_guid");

        $collectionIds = [];
        foreach ($userCollections as $co) {
            $collectionIds[] = $co->collection_guid;
        }

        //get all collections
        $collections = $dbh->getFromDatabase(
            new Collection(),
            "guid IN (" . implode(',:', array_keys($collectionIds)) . ")",
            $collectionIds);

        //save them in temp collections
        $this->tempCollections = array();
        foreach ($collections as $collection) {
            $this->tempCollections[$collection->guid] = $collection;
        }
    }

    /**
     * @param Collection $coll
     * @return bool|ContentVersion
     */
    private function getActiveVersion(Collection $coll)
    {
        return $this->getDatabaseHelper()->getSingleFromDatabase(
            new ContentVersion(),
            "content_type = :content_type AND entity_guid = :entity_guid",
            array("content_type" => ContentType::COLLECTION, "entity_guid" => $coll->guid),
            "create_date_time DESC"
        );
    }
}
