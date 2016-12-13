<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 09.12.2016
 * Time: 08:44
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Models\Communication\Entities\SyncCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\HistoryEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\Models\Communication\Response\HistoryEntityResponse;
use Famoser\SyncApi\Models\Communication\Response\SyncEntityResponse;
use Famoser\SyncApi\Tests\AssertHelper;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\SampleGenerator;
use Famoser\SyncApi\Tests\ApiTestHelper;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * test the api collection nodes
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class EntityControllerHistorySyncTest extends ApiTestController
{
    private $cache;

    /**
     * @param $count
     */
    private function addEntityVersion($count)
    {
        if ($count <= 0) {
            return;
        }

        $action = OnlineAction::UPDATE;
        if ($this->cache == null) {
            $this->cache = [];
            $this->cache["UserId"] = $this->testHelper->getUserId();
            $this->cache["DeviceId"] = $this->testHelper->getDeviceId($this->cache["UserId"]);
            $this->cache["CollectionId"] = $this->testHelper->getCollectionId($this->cache["UserId"], $this->cache["DeviceId"]);
            $this->cache["EntityId"] = SampleGenerator::createGuid();
            $this->cache["VersionIds"] = [];
            $action = OnlineAction::CREATE;
        }
        //test create
        $syncRequest = new SyncEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->cache["UserId"];
        $syncRequest->DeviceId = $this->cache["DeviceId"];

        $collEntity = new SyncCommunicationEntity();
        SampleGenerator::createEntity($collEntity);
        $collEntity->Id = $this->cache["EntityId"];
        $collEntity->UserId = $syncRequest->UserId;
        $collEntity->DeviceId = $syncRequest->DeviceId;
        $collEntity->CollectionId = $this->cache["CollectionId"];
        $collEntity->Content = uniqid();
        $collEntity->VersionId = SampleGenerator::createGuid();
        $this->cache["VersionIds"][] = $collEntity->VersionId;
        $collEntity->OnlineAction = $action;

        $syncRequest->SyncEntities[] = $collEntity;

        $this->testHelper->mockApiRequest($syncRequest, "entities/sync");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
        AssertHelper::checkForSavedEntity($this, $collEntity, $this->testHelper->getTestApp());

        $this->addEntityVersion(--$count);
    }

    /**
     * tests single create
     */
    public function testReadHistory()
    {
        $this->addEntityVersion(2);

        $request = new HistoryEntityRequest();
        $this->testHelper->authorizeRequest($request);
        $request->UserId = $this->cache["UserId"];
        $request->DeviceId = $this->cache["DeviceId"];
        $request->Id = $this->cache["EntityId"];

        $this->testHelper->mockApiRequest($request, "entities/history/sync");
        $response = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);
        /* @var HistoryEntityResponse $responseObj */
        $responseObj = json_decode($responseString);

        static::assertTrue(count($responseObj->CollectionEntities) == 2);
    }

    /**
     * tests single create
     */
    public function testReadHistory2()
    {
        $this->addEntityVersion(3);

        $request = new HistoryEntityRequest();
        $this->testHelper->authorizeRequest($request);
        $request->UserId = $this->cache["UserId"];
        $request->DeviceId = $this->cache["DeviceId"];
        $request->VersionIds = $this->cache["VersionIds"];
        $request->Id = $this->cache["EntityId"];

        $this->testHelper->mockApiRequest($request, "entities/history/sync");
        $response = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);
        /* @var HistoryEntityResponse $responseObj */
        $responseObj = json_decode($responseString);

        static::assertTrue(!(is_array($responseObj->CollectionEntities) && count($responseObj->CollectionEntities)));
    }
}