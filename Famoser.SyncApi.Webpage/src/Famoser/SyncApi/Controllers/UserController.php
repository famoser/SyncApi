<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Models\Communication\Response\AuthorizationResponse;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Types\ApiError;
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
        if (!in_array($req->CollectionEntity->Id, $allowedGuids)) {
            throw new ApiException(ApiError::USER_NOT_AUTHORIZED);
        }

        //check if user already in collection
        $userCollection = $this->getDatabaseService()->getSingleFromDatabase(
            new UserCollection(),
            "user_guid =:user_guid AND collection_guid = :collection_guid",
            ["user_guid" => $req->UserEntity->Id, "collection_guid" => $req->CollectionEntity->Id]
        );

        //user has not already access to collection, add him
        if ($userCollection == null) {
            $userCollection = new UserCollection();
            $userCollection->user_guid = $req->UserEntity->Id;
            $userCollection->create_date_time = time();
            $userCollection->collection_guid = $req->CollectionEntity->Id;
            if (!$this->getDatabaseService()->saveToDatabase($userCollection)) {
                throw new ServerException(ServerError::DATABASE_SAVE_FAILURE);
            }
        }

        return $this->returnJson($response, new AuthorizationResponse());
    }
}
