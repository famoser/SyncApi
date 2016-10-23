<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 29/05/2016
 * Time: 18:30
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Helpers\FormatHelper;
use Famoser\SyncApi\Helpers\GuidHelper;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Models\Entities\Content;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Models\Response\CollectionEntriesResponse;
use Famoser\SyncApi\Models\Response\ContentEntityHistoryResponse;
use Famoser\SyncApi\Models\Response\Entities\CollectionEntryEntity;
use Famoser\SyncApi\Models\Response\Entities\HistoryEntry;
use Famoser\SyncApi\Models\Response\Entities\RefreshEntity;
use Famoser\SyncApi\Models\Response\RefreshResponse;
use Famoser\SyncApi\Models\Response\UpdateResponse;
use Famoser\SyncApi\Types\ApiErrorTypes;
use Famoser\SyncApi\Types\ServerVersion;
use Slim\Http\Request;
use Slim\Http\Response;
use Upload\File;
use Upload\Storage\FileSystem;

class SyncController extends BaseController
{
    public function sync(Request $request, Response $response, $args)
    {
        $model = RequestHelper::parseSyncRequest($request);
        if ($this->isAuthorized($model)) {
            if (!$this->isWellDefined($model, null, array("RefreshEntities", "CollectionIds")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $contentIds = [];
            $contentVersionById = [];
            foreach ($model->RefreshEntities as $refreshEntity) {
                $contentIds[] = $refreshEntity->ContentId;
                $contentVersionById[$refreshEntity->ContentId] = $refreshEntity->VersionId;
            }

            $helper = $this->getDatabaseHelper();
            $results = $helper->getWithInFromDatabase(new Content(), "content_id", $contentIds, false, null, null, "content_id, creation_date_time DESC");

            $resp = new RefreshResponse();
            $resp->RefreshEntities = [];
            $collectionIds = [];
            $foundIds = [];
            //updating info of existing
            for ($i = 0; $i < count($results); $i++) {
                if (!in_array($results[$i]->collection_id, $collectionIds))
                    $collectionIds[] = $results[$i]->collection_id;
                $foundIds[] = $results[$i]->content_id;

                if ($contentVersionById[$results[$i]->content_id] != $results[$i]->version_id) {
                    $entity = new RefreshEntity();
                    $entity->VersionId = $results[$i]->version_id;
                    $entity->ContentId = $results[$i]->content_id;
                    $entity->CollectionId = $results[$i]->collection_id;
                    //check if requested version is already on server
                    $found = false;
                    for (; $i < count($results); $i++) {
                        if ($results[$i]->content_id != $entity->ContentId) { //break out if not same content anymore
                            $i--;
                            break;
                        }
                        if ($contentVersionById[$results[$i]->content_id] != $results[$i]->version_id) {
                            $found = true;
                        }
                    }

                    if ($found)
                        $entity->RemoteStatus = ServerVersion::Older;
                    else
                        $entity->RemoteStatus = ServerVersion::Newer;

                    $resp->RefreshEntities[] = $entity;
                } else {
                    $currentContentId = $results[$i]->content_id;
                    //skip to next content type
                    for (; $i < count($results); $i++) {
                        if ($results[$i]->content_id != $currentContentId) { //break out if not same content anymore
                            $i--;
                            break;
                        }
                    }
                }
            }

            foreach ($model->CollectionIds as $collectionId) {
                if (!in_array($collectionId, $collectionIds))
                    $collectionIds[] = $collectionId;
            }

            //adding missing from database
            $missings = $helper->getFromDatabase(new Content(), "content_id NOT IN (" . implode(",", $contentIds) . ") AND collection_id IN (" . implode(",", $collectionIds) . ")", null, "content_id, creation_date_time DESC");
            foreach ($missings as $missing) {
                $entity = new RefreshEntity();
                $entity->VersionId = $missing->version_id;
                $entity->ContentId = $missing->content_id;
                $entity->CollectionId = $missing->collection_id;
                //check if requested version is already on server
                $entity->RemoteStatus = ServerVersion::Newer;
                $resp->RefreshEntities[] = $entity;

                for (; $i < count($results); $i++) {
                    if ($results[$i]->content_id != $entity->ContentId) { //break out if not same content anymore
                        $i--;
                        break;
                    }
                }
            }

            //add missing from request
            foreach ($model->RefreshEntities as $refreshEntity) {
                if (!in_array($refreshEntity->ContentId, $foundIds)) {
                    $entity = new RefreshEntity();
                    $entity->VersionId = $refreshEntity->VersionId;
                    $entity->ContentId = $refreshEntity->ContentId;
                    $entity->CollectionId = $refreshEntity->CollectionId;
                    $entity->RemoteStatus = ServerVersion::None;
                    $resp->RefreshEntities[] = $entity;
                }
            }

            return ResponseHelper::getJsonResponse($response, $resp);
        } else {
            return $this->returnApiError(ApiErrorTypes::NotAuthorized, $response);
        }
    }

    public function update(Request $request, Response $response, $args)
    {
        $model = RequestHelper::parseUpdateRequest($request);
        if ($this->isAuthorized($model)) {
            if (!$this->isWellDefined($model, array("CollectionId", "ContentId", "VersionId")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $helper = $this->getDatabaseHelper();
            $exiting = $helper->getSingleFromDatabase(new Content(), "content_id=:content_id AND version_id=:version_id", array("content_id" => $model->ContentId, "version_id" => $model->VersionId));
            if ($exiting != null)
                return $this->returnApiError(ApiErrorTypes::InvalidVersionId, $response);

            //save file
            $storage = new FileSystem($this->getUserDirForContent($this->getAuthorizedUser($model)->user_id));
            $file = new File('updateFile', $storage);
            $file->setName($this->getFilenameForContent($model->ContentId, $model->VersionId));
            $file->upload();

            //update database
            $newModel = new Content();
            $newModel->device_id = $model->DeviceId;
            $newModel->user_id = $this->getAuthorizedUser($model)->user_id;
            $newModel->collection_id = $model->CollectionId;
            $newModel->content_id = $model->ContentId;
            $newModel->creation_date_time = time();
            $newModel->version_id = $model->VersionId;
            if (!$helper->saveToDatabase($newModel)) {
                return $this->returnApiError(ApiErrorTypes::DatabaseFailure, $response);
            }

            $resp = new UpdateResponse();
            return ResponseHelper::getJsonResponse($response, $resp);
        } else {
            return $this->returnApiError(ApiErrorTypes::NotAuthorized, $response);
        }
    }

    public function readContentEntity(Request $request, Response $response, $args)
    {
        $model = RequestHelper::parseContentEntityRequest($request);
        if ($this->isAuthorized($model)) {
            if (!$this->isWellDefined($model, array("VersionId", "ContentId")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $path = $this->getPathForContent($this->getAuthorizedUser($model)->user_id, $model->ContentId, $model->VersionId);
            if (!file_exists($path)) {
                return $this->returnApiError(ApiErrorTypes::ContentNotFound, $response, $path);
            }
            $content = file_get_contents($path);
            return $response->getBody()->write($content);
        } else {
            return $this->returnApiError(ApiErrorTypes::NotAuthorized, $response);
        }
    }

    public function getHistory(Request $request, Response $response, $args)
    {
        $model = RequestHelper::parseContentEntityHistoryRequest($request);
        if ($this->isAuthorized($model)) {
            if (!$this->isWellDefined($model, array("ContentId")))
                return $this->returnApiError(ApiErrorTypes::NotWellDefined, $response);

            $helper = $this->getDatabaseHelper();

            //get all entries
            $historyEntries = $helper->getFromDatabase(new Content(), "content_id=:content_id", array("content_id" => $model->ContentId), "creation_date_time DESC");

            //cache all devices
            $deviceIds = [];
            foreach ($historyEntries as $historyEntry) {
                if (!in_array($historyEntry->device_id, $deviceIds))
                    $deviceIds[] = $historyEntry->device_id;
            }
            $helper = $this->getDatabaseHelper();
            $devices = $helper->getWithInFromDatabase(new Device(), "id", $deviceIds);
            $deviceIdAdapter = [];
            foreach ($devices as $device) {
                $deviceIdAdapter[$device->id] = $device->device_id;
            }

            //create response
            $resp = new ContentEntityHistoryResponse();
            $resp->HistoryEntries = [];
            foreach ($historyEntries as $historyEntry) {
                $entity = new HistoryEntry();
                $entity->CreationDateTime = FormatHelper::toCSharpDateTime($historyEntry->creation_date_time);
                $entity->VersionId = $historyEntry->version_id;
                $entity->DeviceId = $deviceIdAdapter[$historyEntry->device_id];
                $resp->HistoryEntries[] = $entity;
            }
            return ResponseHelper::getJsonResponse($response, $resp);

        } else {
            return $this->returnApiError(ApiErrorTypes::NotAuthorized, $response);
        }
    }

    private function getPathForContent($userGuid, $contentGuid, $version)
    {
        return $this->getUserDirForContent($userGuid) . "/" . $this->getFilenameForContent($contentGuid, $version) . "." . $this->getExtensionForContent();
    }

    private function getFilenameForContent($contentGuid, $version)
    {
        return $contentGuid . "_" . $version;
    }

    private function getExtensionForContent()
    {
        //empty fileextension
        return "";
    }

    private function getUserDirForContent($userGuid)
    {
        $path = $this->container->get("settings")["file_path"] . "/" . $userGuid;
        if (!is_dir($path)) {
            mkdir($path);
        }
        return $path;
    }
}