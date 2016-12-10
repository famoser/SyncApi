<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 11.11.2016
 * Time: 15:30
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\OnlineAction;
use Famoser\SyncApi\Types\ServerError;

/**
 * Base class for all api sync requests
 *
 * @package Famoser\SyncApi\Controllers\Base
 */
abstract class ApiSyncController extends ApiRequestController
{
    /**
     * get all entities the user has access to
     *
     * @param BaseRequest $req
     * @param $contentType
     * @return BaseSyncEntity[]
     */
    abstract protected function getAllFromDatabase(BaseRequest $req, $contentType);

    /**
     * create a new entity ready to insert into database
     *
     * @param BaseRequest $req
     * @param $contentType
     * @param BaseCommunicationEntity $commEntity
     * @return BaseSyncEntity
     */
    abstract protected function createEntity(BaseRequest $req, $contentType, BaseCommunicationEntity $commEntity);

    /**
     * does the sync in a generic fashion
     *
     * @param BaseRequest $req
     * @param BaseSyncEntity[] $commEntities
     * @param $contentType
     * @param array $allowedOA
     * @return \Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity[]
     * @throws ApiException
     * @throws ServerException
     */
    protected function syncInternal(
        BaseRequest $req,
        array $commEntities,
        $contentType,
        array $allowedOA = OnlineAction::ALL_SYNC_ACTIONS
    )
    {
        $resultArray = [];
        $askedForGuids = [];
        foreach ($commEntities as $communicationEntity) {
            $askedForGuids[] = $communicationEntity->Id;

            //check that no action can be executed which is not explicitly allowed
            if (!in_array($communicationEntity->OnlineAction, $allowedOA)) {
                $this->getLoggingService()->log(json_encode($allowedOA), "debug.txt");
                $this->getLoggingService()->log(json_encode($communicationEntity), "excel.txt");
                throw new ApiException(ApiError::ACTION_PROHIBITED);
            }


            //execute the OnlineAction
            switch ($communicationEntity->OnlineAction) {
                case OnlineAction::NONE:
                    continue;
                case OnlineAction::CREATE:
                    $entity = $this->getByIdInternal($req, $communicationEntity->Id, $contentType);
                    $this->createSyncEntity($req, $communicationEntity, $contentType, $entity);
                    break;
                case OnlineAction::READ:
                    $entity = $this->getByIdInternal($req, $communicationEntity->Id, $contentType);
                    $resultArray[] = $this->readSyncEntity($contentType, $entity);
                    break;
                case OnlineAction::UPDATE:
                    $entity = $this->getByIdInternal($req, $communicationEntity->Id, $contentType);
                    $this->updateSyncEntity($communicationEntity, $entity);
                    break;
                case OnlineAction::DELETE:
                    $entity = $this->getByIdInternal($req, $communicationEntity->Id, $contentType);
                    $this->deleteSyncEntity($entity);
                    break;
                case OnlineAction::CONFIRM_VERSION:
                    $entity = $this->getByIdInternal($req, $communicationEntity->Id, $contentType);
                    $res = $this->confirmVersion($communicationEntity, $contentType, $entity);
                    if ($res != null) {
                        $resultArray[] = $res;
                    }
                    break;
                case OnlineAction::CONFIRM_ACCESS:
                default:
                    throw new ApiException(ApiError::ACTION_NOT_SUPPORTED);
            }
        }

        //add new ones
        $existingEntities = $this->getAllInternal($req, $contentType);
        $existingEntityIds = $this->getArrayOfObjectProperty($existingEntities, "guid");

        //get new Ids
        $newOnes = array_diff($askedForGuids, $existingEntityIds);

        if (count($newOnes) > 0) {
            //add new Objects to response

            //traverse list only once as array_diff returns sorted array (not sure about this one)
            $ind = 0;
            foreach ($newOnes as $newOne) {
                for (; $ind < count($existingEntityIds); $ind++) {
                    if ($existingEntityIds[$ind] != $newOne) {
                        //not the Id we are looking for; skip
                        continue;
                    }
                    if (!$existingEntities[$ind]->is_deleted) {
                        $ver = $this->getActiveVersion($existingEntities[$ind], $contentType);
                        $resultArray[] = $existingEntities[$ind]->createCommunicationEntity($ver, OnlineAction::CREATE);
                    }
                    break;
                }
            }
        }

        return $resultArray;
    }

    private $allEntities;
    /**
     * get all collections accessible by the current user
     *
     * @param  BaseRequest $req
     * @param $contentType
     * @return \Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity[]
     */
    private function getAllInternal(BaseRequest $req, $contentType)
    {
        if ($this->allEntities == null) {
            $dbData = $this->getAllFromDatabase($req, $contentType);
            $this->allEntities = [];
            foreach ($dbData as $item) {
                $this->allEntities[$item->guid] = $item;
            }
        }
        return $this->allEntities;
    }

    /**
     * returns an array which value are the specified property of the object.
     *
     * @param array $objects
     * @param $property
     * @return array
     */
    private function getArrayOfObjectProperty(array $objects, $property)
    {
        $newArr = [];
        foreach ($objects as $object) {
            /** @noinspection PhpVariableVariableInspection */
            $newArr[] = $object->$property;
        }
        return $newArr;
    }

    /**
     * get a collection by a guid accessible for the user
     *
     * @param  BaseRequest $req
     * @param $guid
     * @param $contentType
     * @return BaseSyncEntity
     */
    private function getByIdInternal(BaseRequest $req, $guid, $contentType)
    {
        $tempEntities = $this->getAllInternal($req, $contentType);
        return in_array($guid, array_keys($tempEntities)) ? $tempEntities[$guid] : null;
    }

    /**
     * @param BaseSyncEntity $syncEntity
     * @param $contentType
     * @return bool|ContentVersion
     */
    private function getActiveVersion(BaseSyncEntity $syncEntity, $contentType)
    {
        return $this->getDatabaseService()->getSingleFromDatabase(
            new ContentVersion(),
            "content_type = :content_type AND entity_guid = :entity_guid",
            ["content_type" => $contentType, "entity_guid" => $syncEntity->guid],
            "create_date_time DESC, id DESC"
        );
    }

    /**
     * confirms if the entity is already the newest version. If not, returns the newer version
     *
     * @param BaseCommunicationEntity $communicationEntity
     * @param $contentType
     * @param BaseSyncEntity $entity
     * @return BaseCommunicationEntity|null
     * @throws ApiException
     */
    private function confirmVersion(BaseCommunicationEntity $communicationEntity, $contentType, BaseSyncEntity $entity)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $ver = $this->getActiveVersion($entity, $contentType);

        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        if ($entity->is_deleted) {
            return $entity->createCommunicationEntity($ver, OnlineAction::DELETE);
        } elseif ($communicationEntity->VersionId != $ver->version_guid) {
            return $entity->createCommunicationEntity($ver, OnlineAction::UPDATE);
        }
        return null;
    }

    /**
     * creates a new entity to be inserted into the database
     *
     * @param BaseRequest $req
     * @param BaseSyncEntity $syncEntity
     * @param BaseCommunicationEntity $commEntity
     * @param $contentType
     * @throws ApiException
     * @throws ServerException
     */
    private function createSyncEntity(
        BaseRequest $req,
        BaseCommunicationEntity $commEntity,
        $contentType,
        BaseSyncEntity $syncEntity = null
    )
    {
        if ($syncEntity != null) {
            //this happens if id guid is set twice. can not happen under normal circumstances
            throw new ApiException(ApiError::RESOURCE_ALREADY_EXISTS);
        }

        $syncEntity = $this->createEntity($req, $contentType, $commEntity);
        $syncEntity->guid = $commEntity->Id;
        $syncEntity->identifier = $commEntity->Identifier;
        $syncEntity->is_deleted = false;

        if (!$this->getDatabaseService()->saveToDatabase($syncEntity)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }

        $content = $syncEntity->createContentVersion($commEntity);
        if (!$this->getDatabaseService()->saveToDatabase($content)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }
    }

    /**
     * reads the active version of the specified entity from database
     *
     * @param BaseSyncEntity $entity
     * @param $contentType
     * @return BaseCommunicationEntity
     * @throws ApiException
     */
    private function readSyncEntity($contentType, BaseSyncEntity $entity = null)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $ver = $this->getActiveVersion($entity, $contentType);

        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        if ($entity->is_deleted) {
            return $entity->createCommunicationEntity($ver, OnlineAction::DELETE);
        }
        return $entity->createCommunicationEntity($ver, OnlineAction::READ);
    }

    /**
     * updates sync entity, by creating a new content version
     *
     * @param BaseCommunicationEntity $syncEntity
     * @param BaseSyncEntity $entity
     * @throws ApiException
     * @throws ServerException
     */
    protected function updateSyncEntity(BaseCommunicationEntity $syncEntity, BaseSyncEntity $entity = null)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        //un-delete if already deleted
        if ($entity->is_deleted) {
            $entity->is_deleted = false;
            if (!$this->getDatabaseService()->saveToDatabase($entity)) {
                throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
            }
        }

        $content = $entity->createContentVersion($syncEntity);
        if (!$this->getDatabaseService()->saveToDatabase($content)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }
    }

    /**
     * marks sync entity as deleted
     *
     * @param BaseSyncEntity $entity
     * @throws ApiException
     * @throws ServerException
     */
    protected function deleteSyncEntity(BaseSyncEntity $entity = null)
    {
        if ($entity == null) {
            throw new ApiException(ApiError::RESOURCE_NOT_FOUND);
        }

        $entity->is_deleted = true;
        if (!$this->getDatabaseService()->saveToDatabase($entity)) {
            throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
        }
    }
}
