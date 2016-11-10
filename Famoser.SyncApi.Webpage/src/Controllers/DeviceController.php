<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Types\OnlineAction;
use Slim\Http\Request;
use Slim\Http\Response;

class DeviceController extends ApiRequestController
{
    public function get(Request $request, Response $response, $args)
    {
        $req = RequestHelper::parseCollectionEntityRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        $devices = $this->getDatabaseHelper()->getFromDatabase(
            new Device(),
            "user_guid = :user_guid AND is_deleted =:is_deleted",
            array("user_guid" => $this->getUser($req)->guid, "is_deleted" => false)
        );

        $devicesByGuid = [];
        foreach ($devices as $device) {
            $devicesByGuid[$device->guid] = $device;
        }

        $resp = new CollectionEntityResponse();
        foreach ($req->CollectionEntities as $collectionEntity) {
            $entity = $collectionEntity;
            $device = array_key_exists($entity->Id, $devicesByGuid) ? $devicesByGuid[$entity->Id] : null;
            if ($entity->OnlineAction == OnlineAction::CONFIRM_VERSION) {
                if ($device == null) {
                    $ce = new CollectionEntity();
                    $ce->OnlineAction = OnlineAction::DELETE;
                    $ce->Id = $entity->Id;
                    $resp->CollectionEntities[] = $ce;
                } else {
                    $ver = $this->getDatabaseHelper()->getSingleFromDatabase(
                        new ContentVersion(), "entity_guid = :entity_guid AND content_type = :content_type",
                        array("entity_guid" => $entity->Id)
                    );
                    //todo: return as update, implement create

                }
            }
        }

        throw new \Exception("not implemented");
    }

    public function auth(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }

    public function unAuth(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }
}
