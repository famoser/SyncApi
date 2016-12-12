<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12.12.2016
 * Time: 14:50
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Response\CollectionEntityResponse;
use Famoser\SyncApi\Tests\AssertHelper;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiSyncTestController;

/**
 * tests the methods from the device controller
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class DeviceControllerTest extends ApiSyncTestController
{
    /**
     * tests if all devices are returned.
     * Will not test all sync properties as this is already done multiple times in other controller tests
     */
    public function testGetAllDevices()
    {
        //add unauthorized device
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $deviceId1 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $deviceId2 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $deviceId3 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $syncRequest->DeviceId = $deviceId1;

        $this->testHelper->mockApiRequest($syncRequest, "devices/get");
        $response = $this->testHelper->getTestApp()->run();

        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 3);
    }

    /**
     * tests if all devices are returned.
     * Will not test all sync properties as this is already done multiple times in other controller tests
     */
    public function testAuthenticateDevices()
    {
        //add unauthorized device
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $deviceId1 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $deviceId2 = $this->testHelper->getDeviceId($syncRequest->UserId, false);
        $deviceId3 = $this->testHelper->getDeviceId($syncRequest->UserId, false);
        $syncRequest->DeviceId = $deviceId1;

        //auth one device
        $authRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authRequest);

        $authRequest->UserId = $syncRequest->UserId;
        $authRequest->DeviceId = $syncRequest->DeviceId;
        $authRequest->ClientMessage = $deviceId2;
        $this->testHelper->mockApiRequest($authRequest, "devices/auth");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);


        $this->testHelper->mockApiRequest($syncRequest, "devices/get");
        $response = $this->testHelper->getTestApp()->run();

        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 3);
        foreach ($responseObj->CollectionEntities as $collectionEntity) {
            /* @var DeviceCommunicationEntity $collectionEntity */
            switch ($collectionEntity->Id) {
                case $deviceId1:
                    static::assertTrue($collectionEntity->IsAuthenticated);
                    break;
                case $deviceId2:
                    static::assertTrue($collectionEntity->IsAuthenticated);
                    break;
                case $deviceId3:
                    static::assertFalse($collectionEntity->IsAuthenticated);
                    break;
                default:
                    static::fail("unknown device id");
            }
        }
    }

    /**
     * tests if all devices are returned.
     * Will not test all sync properties as this is already done multiple times in other controller tests
     */
    public function testUnAuthenticateDevices()
    {
        //add unauthorized device
        $syncRequest = new CollectionEntityRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $deviceId1 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $deviceId2 = $this->testHelper->getDeviceId($syncRequest->UserId);
        $deviceId3 = $this->testHelper->getDeviceId($syncRequest->UserId, false);
        $syncRequest->DeviceId = $deviceId1;

        //auth one device
        $authRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authRequest);

        $authRequest->UserId = $syncRequest->UserId;
        $authRequest->DeviceId = $syncRequest->DeviceId;
        $authRequest->ClientMessage = $deviceId2;
        $this->testHelper->mockApiRequest($authRequest, "devices/unauth");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);


        $this->testHelper->mockApiRequest($syncRequest, "devices/get");
        $response = $this->testHelper->getTestApp()->run();

        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);

        /* @var CollectionEntityResponse $responseObj */
        $responseObj = json_decode($responseString);
        static::assertTrue(count($responseObj->CollectionEntities) == 3);
        foreach ($responseObj->CollectionEntities as $collectionEntity) {
            /* @var DeviceCommunicationEntity $collectionEntity */
            switch ($collectionEntity->Id) {
                case $deviceId1:
                    static::assertTrue($collectionEntity->IsAuthenticated);
                    break;
                case $deviceId2:
                    static::assertFalse($collectionEntity->IsAuthenticated);
                    break;
                case $deviceId3:
                    static::assertFalse($collectionEntity->IsAuthenticated);
                    break;
                default:
                    static::fail("unknown device id");
            }
        }
    }
}