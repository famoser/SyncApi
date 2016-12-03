<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 17:03
 */

namespace Famoser\SyncApi\Tests\ControllerTests;

use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\TestHelper;
use Famoser\SyncApi\Types\OnlineAction;

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
        //arrange
        $syncRequest = new AuthorizationRequest();
        $this->testHelper->authorizeRequest($syncRequest);

        $user = new UserCommunicationEntity();
        $user->PersonalSeed = 621842297;
        $user->Id = "da66416e-767d-4687-a2af-353b47a0e5c1";
        $user->VersionId = "da66416e-767d-4687-a2af-831845bbda8f";
        $user->OnlineAction = OnlineAction::CREATE;
        $user->Content = "{}";
        $user->CreateDateTime = date("c");
        $user->Identifier = "user";
        $syncRequest->UserEntity = $user;
        $syncRequest->UserId = $user->Id;
        $syncRequest->DeviceId = "00000000-0000-0000-0000-000000000000";

        $this->testHelper->mockApiRequest($syncRequest, "auth/sync", $this->app);

        //act
        $response = $this->app->run();

        //assert
        $this->testHelper->checkForSuccessfulApiResponse($this, $response);
    }
}