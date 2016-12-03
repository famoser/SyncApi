<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 17:03
 */

namespace Famoser\SyncApi\Tests\ControllerTests;

use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\TestHelper;

/**
 * Class AuthorizationControllerTests
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class AuthorizationControllerTest extends \PHPUnit_Framework_TestCase
{
    /* @var SyncApiApp $app */
    private $app;
    /* @var TestHelper $testHelper */
    private $testHelper;

    /**
     * create the $app and $testHelper
     */
    public function setUp()
    {
        $this->testHelper = new TestHelper();
        $this->app = $this->testHelper->getTestApp();
    }

    /**
     * cleans the test environment
     */
    public function tearDown()
    {
        $this->testHelper->cleanEnvironment();
    }

    /**
     * tries to create a user for a test application
     */
    public function testCreateUser()
    {
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $this->testHelper->mockApiRequest('
            {
                "UserEntity": {
                    "PersonalSeed": 621842297,
                    "Id": "da66416e-767d-4687-a2af-353b47a0e5c1",
                    "VersionId": "6b73667e-0229-4350-9c0e-831845bbda8f",
                    "OnlineAction": 1,
                    "Content": "{}",
                    "CreateDateTime": "2016-11-28T12:43:13+01:00",
                    "Identifier": "user"
                },
                "DeviceEntity": null,
                "CollectionEntity": null,
                "ClientMessage": null,
                "UserId": "da66416e-767d-4687-a2af-353b47a0e5c1",
                "DeviceId": "00000000-0000-0000-0000-000000000000",
                "AuthorizationCode": "13431239_-8215860",
                "ApplicationId": "test_appl"
            }',
            "auth/sync",
            $this->app
        );

        $response = $this->app->run();

        static::assertEquals('["ApiError":0,"RequestFailed":false,"ServerMessage":null]', $response->getBody());
    }
}