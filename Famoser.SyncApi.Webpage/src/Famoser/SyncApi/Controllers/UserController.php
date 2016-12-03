<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Types\ServerError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * the user controller authenticates users to new collections
 * @package Famoser\SyncApi\Controllers
 */
class UserController extends ApiRequestController
{
    /**
     * authenticate other users against collection
     * 
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws ServerException
     * @throws \Famoser\SyncApi\Exceptions\ApiException
     */
    public function auth(Request $request, Response $response, $args)
    {
        $req = $this->getRequestService()->parseAuthorizationRequest($request);
        $this->authorizeRequest($req);
        $this->authenticateRequest($req);

        //check if requesting user has access to collection, if yes, add to $guidsToSetFree
        $allowedGuids = $this->getCollectionIds($req);
        $guidsToSetFree = [];
        foreach ($req->CollectionEntity as $item) {
            if (in_array($item->Id, $allowedGuids)) {
                $guidsToSetFree[] = $item->Id;
            }
        }

        foreach ($req->UserEntity as $item) {
            //check if user has already access to one or more of the collections
            $sqlArr = $guidsToSetFree;
            $sqlArr["user_guid"] = $item->Id;
            $userCollections = $this->getDatabaseService()->getFromDatabase(
                new UserCollection(),
                "user_guid =:user_guid AND collection_guid IN (:" . implode(",:", array_keys($guidsToSetFree)),
                $sqlArr,
                null,
                1000,
                "collection_guid");

            //remove guids which user already has access to
            $guidsForUser = $guidsToSetFree;
            foreach ($userCollections as $userCollection) {
                //sorry for this clumsy code, but as this corner case should not happen at all, I'll leave it as it is
                $guidsForUser = array_diff([$userCollection->collection_guid], $guidsForUser);
            }

            //add new accesses
            foreach ($guidsForUser as $collectionGuid) {
                $userCollection = new UserCollection();
                $userCollection->user_guid = $item->Id;
                $userCollection->create_date_time = time();
                $userCollection->collection_guid = $collectionGuid;
                if (!$this->getDatabaseService()->saveToDatabase($userCollection)) {
                    throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
                }
            }
        }

        return $this->returnJson($response, new AuthorizationResponse());
    }
}
