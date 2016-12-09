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
use Famoser\SyncApi\Tests\AssertHelper;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\SampleGenerator;

/**
 * test the api collection nodes
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class CollectionControllerTest extends ApiTestController
{
    public function testSync()
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
}