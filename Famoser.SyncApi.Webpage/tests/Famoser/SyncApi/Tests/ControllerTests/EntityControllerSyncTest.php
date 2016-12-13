<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 09.12.2016
 * Time: 08:44
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Models\Communication\Entities\SyncCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\Models\Communication\Response\SyncEntityResponse;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\TestHelpers\AssertHelper;
use Famoser\SyncApi\Tests\TestHelpers\SampleGenerator;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * test the api collection nodes
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class EntityControllerSyncTest extends ApiTestController
{
    /**
     * tests single create
     */
    public function testCreateSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single read if the id of the entity is provided
     */
    public function testExplicitReadSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test read
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new SyncCommunicationEntity();
        $collEntity2->VersionId = $collEntity->VersionId;
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->OnlineAction = OnlineAction::READ;

        $syncRequest2->SyncEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);

        /* @var SyncEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->SyncEntities) == 1);
        static::assertEquals($collEntity->VersionId, $responseObj->SyncEntities[0]->VersionId);
        static::assertEquals($collEntity->Content, $responseObj->SyncEntities[0]->Content);
        static::assertEquals(
            (new \DateTime($collEntity->CreateDateTime))->getTimestamp(),
            (new \DateTime($responseObj->SyncEntities[0]->CreateDateTime))->getTimestamp()
        );
        static::assertEquals($syncRequest->DeviceId, $responseObj->SyncEntities[0]->DeviceId);
        static::assertEquals($collEntity->Id, $responseObj->SyncEntities[0]->Id);
        static::assertEquals($collEntity->Identifier, $responseObj->SyncEntities[0]->Identifier);
        static::assertEquals($syncRequest->UserId, $responseObj->SyncEntities[0]->UserId);
        static::assertEquals(OnlineAction::READ, $responseObj->SyncEntities[0]->OnlineAction);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests read if no id of the missing collection is provided
     */
    public function testImplicitReadSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);
        $collEntity->CollectionId = $collectionId;

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test read
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $syncRequest2->SyncEntities = [];

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);

        /* @var SyncEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->SyncEntities) == 1);
        static::assertEquals(OnlineAction::CREATE, $responseObj->SyncEntities[0]->OnlineAction);
        AssertHelper::checkResponseEntity($this, $collEntity, $syncRequest, $responseObj->SyncEntities[0], $collectionId);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single update
     */
    public function testUpdateSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test update
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity->VersionId = SampleGenerator::createGuid();
        $collEntity->Content = "new_cont";
        $collEntity->OnlineAction = OnlineAction::UPDATE;

        $syncRequest2->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single delete
     */
    public function testDeleteSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test delete
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new SyncCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->OnlineAction = OnlineAction::DELETE;

        $syncRequest2->SyncEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp(), true);
    }

    /**
     * tests confirm access request method
     */
    public function testConfirmAccessSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);
        $collEntity->CollectionId = $collectionId;

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test confirm version (no response)
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new SyncCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->VersionId = $collEntity->VersionId;
        $collEntity2->OnlineAction = OnlineAction::CONFIRM_VERSION;

        $syncRequest2->SyncEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        //test confirm version (active version response)
        $syncRequest2 = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new SyncCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->VersionId = SampleGenerator::createGuid();
        $collEntity2->OnlineAction = OnlineAction::CONFIRM_VERSION;

        $syncRequest2->SyncEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "entities/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        /* @var SyncEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->SyncEntities) == 1);
        static::assertEquals(OnlineAction::UPDATE, $responseObj->SyncEntities[0]->OnlineAction);
        AssertHelper::checkResponseEntity($this, $collEntity, $syncRequest, $responseObj->SyncEntities[0], $collectionId);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests multiple actions
     */
    public function testMultipleActionSync()
    {
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->testHelper->getCollectionId($syncRequest->UserId, $syncRequest->DeviceId);

        $syncRequest->SyncEntities[] = $collEntity;

        $collEntity2 = clone $collEntity;
        $collEntity2->Id = SampleGenerator::createGuid();

        $syncRequest->SyncEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());
        AssertHelper::checkForSavedEntity($this, $collEntity2, $this->testHelper->getTestApp());
    }
}