<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15/12/2016
 * Time: 21:52
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\TestHelpers\AssertHelper;

/**
 * tests the user controller methods
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class UserControllerTest extends ApiTestController
{
    public function testAuth()
    {
        //test create
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        $collEntity->Id = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);
        $syncRequest->CollectionEntity = $collEntity;

        $usrEntity = new UserCommunicationEntity();
        $usrEntity->Id = $this->testHelper->getUserId();
        $syncRequest->UserEntity = $usrEntity;

        $this->testHelper->mockApiRequest($syncRequest, "users/auth");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);

        $containerBase = new ContainerBase($this->testHelper->getTestApp()->getContainer());
        $databaseService = $containerBase->getDatabaseService();


        $userCollection = $databaseService->getSingleFromDatabase(
            new UserCollection(),
            "user_guid = :user_guid AND collection_guid = :collection_guid",
            ["user_guid" => $syncRequest->UserEntity->Id, "collection_guid" => $syncRequest->CollectionEntity->Id]
        );

        static::assertNotNull($userCollection);
    }
}