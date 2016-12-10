<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 17:03
 */

namespace Famoser\SyncApi\Tests\ControllerTests;

use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\AssertHelper;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Tests\SampleGenerator;
use Famoser\SyncApi\Tests\ApiTestHelper;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * Class AuthorizationControllerTests
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class AuthorizationControllerTest extends ApiTestController
{

    /**
     * create a user
     */
    public function testCreateUser()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        //create user
        $user = new UserCommunicationEntity();
        $user->PersonalSeed = 621842297;
        SampleGenerator::createEntity($user);

        $syncRequest->UserEntity = $user;
        $syncRequest->UserId = $user->Id;

        $this->testHelper->mockApiRequest($syncRequest, "auth/sync");

        //act
        $response = $this->testHelper->getTestApp()->run();

        //assert
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
    }

    /**
     * create a device
     */
    public function testCreateDevice()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->DeviceEntity = $device;
        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $device->Id;

        $this->testHelper->mockApiRequest($syncRequest, "auth/sync");

        //act
        $response = $this->testHelper->getTestApp()->run();

        //assert
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
    }

    /**
     * create an authentication code
     */
    public function testGenerateCode()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $this->testHelper->mockApiRequest($syncRequest, "auth/generate");

        //act
        $response = $this->testHelper->getTestApp()->run();

        //assert
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);
        static::assertRegExp("#\"ServerMessage\":\"([a-z])+\"#", $responseString);
    }

    /**
     * test the status of authenticated
     */
    public function testAuthenticatedStatus()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->DeviceEntity = $device;
        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $this->testHelper->mockApiRequest($syncRequest, "auth/status");
        //act
        $response = $this->testHelper->getTestApp()->run();

        //arrange
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
    }

    /**
     * test the status of authenticated
     */
    public function testUserNotFound()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->UserId = SampleGenerator::createGuid();
        $syncRequest->DeviceId = SampleGenerator::createGuid();

        $this->testHelper->mockApiRequest($syncRequest, "auth/status");
        //act
        $response = $this->testHelper->getTestApp()->run();

        //arrange
        AssertHelper::checkForFailedApiResponse($this, $response, ApiError::USER_NOT_FOUND);
    }

    /**
     * test the status of authenticated
     */
    public function testDeviceNotFound()
    {
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = SampleGenerator::createGuid();

        $this->testHelper->mockApiRequest($syncRequest, "auth/status");
        //act
        $response = $this->testHelper->getTestApp()->run();

        //arrange
        AssertHelper::checkForFailedApiResponse($this, $response, ApiError::DEVICE_NOT_FOUND);
    }

    /**
     * add seconds device without authentication code
     */
    public function testAddSecondDevice()
    {
        //add unauthorized device
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->DeviceEntity = $device;
        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $device->Id;
        //add primary device to user (which will be authenticated)
        $this->testHelper->getDeviceId($syncRequest->UserId);

        $this->testHelper->mockApiRequest($syncRequest, "auth/sync");
        $response = $this->testHelper->getTestApp()->run();

        //check for unauthorized device
        AssertHelper::checkForSuccessfulApiResponse($this, $response);

        $authRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authRequest);
        $authRequest->UserId = $syncRequest->UserId;
        $authRequest->DeviceId = $syncRequest->DeviceId;
        $this->testHelper->mockApiRequest($authRequest, "auth/status");

        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForFailedApiResponse($this, $response, ApiError::DEVICE_NOT_AUTHORIZED);
    }

    /**
     * add second device and authorize it
     */
    public function testUseAuthenticationCode()
    {
        //adds unauthenticated device
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $device = new DeviceCommunicationEntity();
        SampleGenerator::createEntity($device);

        $syncRequest->DeviceEntity = $device;
        $syncRequest->UserId = $this->testHelper->getUserId();
        $syncRequest->DeviceId = $device->Id;
        //add primary device to user (which will be authenticated)
        $authenticatedDeviceId = $this->testHelper->getDeviceId($syncRequest->UserId);

        $this->testHelper->mockApiRequest($syncRequest, "auth/sync");
        $response = $this->testHelper->getTestApp()->run();

        //get auth code
        $authCodeRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authCodeRequest);

        $authCodeRequest->UserId = $syncRequest->UserId;
        $authCodeRequest->DeviceId = $authenticatedDeviceId;

        $this->testHelper->mockApiRequest($authCodeRequest, "auth/generate");
        $response = $this->testHelper->getTestApp()->run();
        $responseString = AssertHelper::checkForSuccessfulApiResponse($this, $response);
        static::assertRegExp("#\"ServerMessage\":\"([a-z])+\"#", $responseString);
        $responseObj = json_decode($responseString);
        $authCode = $responseObj->ServerMessage;

        //auth device with auth code
        $useAuthCodeRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($useAuthCodeRequest);

        $authCodeRequest->UserId = $syncRequest->UserId;
        $authCodeRequest->DeviceId = $syncRequest->DeviceId;
        $authCodeRequest->ClientMessage = $authCode;

        $this->testHelper->mockApiRequest($authCodeRequest, "auth/use");
        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);

        //check if device authenticated now
        AssertHelper::checkForSuccessfulApiResponse($this, $response);

        $authRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authRequest);
        $authRequest->UserId = $syncRequest->UserId;
        $authRequest->DeviceId = $syncRequest->DeviceId;
        $this->testHelper->mockApiRequest($authRequest, "auth/status");

        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForSuccessfulApiResponse($this, $response);
    }
}