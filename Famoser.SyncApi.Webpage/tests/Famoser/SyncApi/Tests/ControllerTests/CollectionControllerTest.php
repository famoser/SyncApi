<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 09.12.2016
 * Time: 08:44
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Tests\AssertHelper;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\SampleGenerator;
use Famoser\SyncApi\Tests\TestHelper;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * test the api collection nodes
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class CollectionControllerTest extends ApiTestController
{
    /**
     * tests single create
     */
    public function testCreateSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single read if the id of the entity is provided
     */
    public function testExplicitReadSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test read
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new CollectionCommunicationEntity();
        $collEntity2->VersionId = $collEntity->VersionId;
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->OnlineAction = OnlineAction::READ;

        $syncRequest2->CollectionEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 1);
        static::assertEquals($collEntity->VersionId, $responseObj->CollectionEntities[0]->VersionId);
        static::assertEquals($collEntity->Content, $responseObj->CollectionEntities[0]->Content);
        static::assertEquals(
            (new \DateTime($collEntity->CreateDateTime))->getTimestamp(),
            (new \DateTime($responseObj->CollectionEntities[0]->CreateDateTime))->getTimestamp()
        );
        static::assertEquals($syncRequest->DeviceId, $responseObj->CollectionEntities[0]->DeviceId);
        static::assertEquals($collEntity->Id, $responseObj->CollectionEntities[0]->Id);
        static::assertEquals($collEntity->Identifier, $responseObj->CollectionEntities[0]->Identifier);
        static::assertEquals($syncRequest->UserId, $responseObj->CollectionEntities[0]->UserId);
        static::assertEquals(OnlineAction::READ, $responseObj->CollectionEntities[0]->OnlineAction);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests read if no id of the missing collection is provided
     */
    public function testImplicitReadSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test read
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $syncRequest2->CollectionEntities = [];

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 1);
        static::assertEquals(OnlineAction::CREATE, $responseObj->CollectionEntities[0]->OnlineAction);
        AssertHelper::checkResponseCollection($this, $collEntity, $syncRequest, $responseObj->CollectionEntities[0]);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single update
     */
    public function testUpdateSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test update
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity->VersionId = SampleGenerator::createGuid();
        $collEntity->Content = "new_cont";
        $collEntity->OnlineAction = OnlineAction::UPDATE;

        $syncRequest2->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());
    }

    /**
     * tests single delete
     */
    public function testDeleteSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test delete
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new CollectionCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->OnlineAction = OnlineAction::DELETE;

        $syncRequest2->CollectionEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp(), true);
    }

    /**
     * tests confirm access request method
     */
    public function testConfirmAccessSync()
    {
        //test create
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $collEntity = new CollectionCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->DeviceId = $syncRequest->DeviceId;

        $syncRequest->CollectionEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "collections/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test confirm version (no response)
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new CollectionCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->VersionId = $collEntity->VersionId;
        $collEntity2->OnlineAction = OnlineAction::CONFIRM_VERSION;

        $syncRequest2->CollectionEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        //test confirm version (active version response)
        $syncRequest2 = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest2);

        $syncRequest2->UserId = $syncRequest->UserId;
        $syncRequest2->DeviceId = $syncRequest->DeviceId;

        $collEntity2 = new CollectionCommunicationEntity();
        $collEntity2->Id = $collEntity->Id;
        $collEntity2->VersionId = SampleGenerator::createGuid();
        $collEntity2->OnlineAction = OnlineAction::CONFIRM_VERSION;

        $syncRequest2->CollectionEntities[] = $collEntity2;

        $this->testHelper->mockApiRequest($syncRequest2, "collections/sync");
        $response2 = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response2);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 1);
        static::assertEquals(OnlineAction::UPDATE, $responseObj->CollectionEntities[0]->OnlineAction);
        AssertHelper::checkResponseCollection($this, $collEntity, $syncRequest, $responseObj->CollectionEntities[0]);
        AssertHelper::checkForSavedCollection($this, $collEntity, $this->testHelper->getTestApp());
    }
}