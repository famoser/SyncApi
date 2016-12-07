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
use Famoser\SyncApi\Tests\SampleGenerator;
use Famoser\SyncApi\Tests\TestHelper;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * Class AuthorizationControllerTests
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class AuthorizationControllerTest extends \PHPUnit_Framework_TestCase
{
    /* @var TestHelper $testHelper */
    private $testHelper;

    /**
     * create the $app and $testHelper
     */
    public function setUp()
    {
        $this->testHelper = new TestHelper();
    }

    /**
     * cleans the test environment
     */
    public function tearDown()
    {
        $this->testHelper->cleanEnvironment();
    }

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
     * create a authentication code
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
     * create a authentication code
     */
    public function testStatus()
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
     * create a authentication code
     */
    public function testAddSecondDevice()
    {
        //arrange
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
        //act
        $response = $this->testHelper->getTestApp()->run();

        //arrange
        AssertHelper::checkForSuccessfulApiResponse($this, $response);

        $authRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($authRequest);
        $authRequest->UserId = $syncRequest->UserId;
        $authRequest->DeviceId = $syncRequest->DeviceId;
        $this->testHelper->mockApiRequest($authRequest, "auth/status");

        $response = $this->testHelper->getTestApp()->run();
        AssertHelper::checkForFailedApiResponse($this, $response, ApiError::DEVICE_NOT_AUTHORIZED);
    }
}