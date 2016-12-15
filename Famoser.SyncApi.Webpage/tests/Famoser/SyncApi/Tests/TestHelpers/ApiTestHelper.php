<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 19:51
 */

namespace Famoser\SyncApi\Tests\TestHelpers;


use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Tests\TestHelpers\Base\BaseTestHelper;
use Famoser\SyncApi\Types\ContentType;
use Slim\Http\Environment;

/**
 * helps preparing the test cases
 *
 * @package Famoser\SyncApi\Tests
 */
class ApiTestHelper extends BaseTestHelper
{
    const TEST_APPLICATION_ID = "test_app";
    const TEST_APPLICATION_SEED = 0;

    private $mockAlreadyCalled;

    /**
     * mock a json POST request
     * call app->run afterwards
     *
     * @param BaseRequest $request
     * @param $relativeLink
     * @param bool $autoReset
     */
    public function mockApiRequest(BaseRequest $request, $relativeLink, $autoReset = true)
    {
        if ($this->mockAlreadyCalled && $autoReset) {
            $this->resetApplication();
        }
        $this->mockAlreadyCalled = true;
        $json = json_encode($request, JSON_PRETTY_PRINT);
        $this->getTestApp()->overrideEnvironment(
            Environment::mock(
                [
                    'REQUEST_METHOD' => 'POST',
                    'REQUEST_URI' => '/1.0/' . $relativeLink,
                    'MOCK_POST_DATA' => $json,
                    'SERVER_NAME' => 'localhost',
                    'CONTENT_TYPE' => 'application/json;charset=utf8'
                ]
            )
        );
    }

    /**
     * mock a json POST request
     * call app->run afterwards
     *
     * @param $relativeLink
     * @param bool $autoReset
     * @internal param BaseRequest $request
     */
    public function mockGetRequest($relativeLink, $autoReset = true)
    {
        if ($this->mockAlreadyCalled && $autoReset) {
            $this->resetApplication();
        }
        $this->mockAlreadyCalled = true;
        $this->getTestApp()->overrideEnvironment(
            Environment::mock(
                [
                    'REQUEST_URI' => '/' . $relativeLink,
                    'SERVER_NAME' => 'localhost'
                ]
            )
        );
    }

    /* @var Application $application */
    private $application;

    /**
     * prepares the environment
     */
    protected function prepareDatabase()
    {
        //create test application
        $application = new Application();
        $application->application_id = static::TEST_APPLICATION_ID;
        $application->application_seed = static::TEST_APPLICATION_SEED;
        $application->description = "a test application created while running tests";
        $application->name = "Test Application";
        $application->release_date_time = time() - 1;
        $this->getDatabaseService()->saveToDatabase($application);

        $this->application = $application;
    }

    /**
     * @return Application
     */
    public function getApiApplication()
    {
        return $this->application;
    }

    /**
     * fills out the application id & authorization code for the request
     *
     * @param BaseRequest $syncRequest
     */
    public function authorizeRequest(BaseRequest $syncRequest)
    {
        $syncRequest->ApplicationId = static::TEST_APPLICATION_ID;
        $syncRequest->AuthorizationCode = 0;
    }

    /**
     * returns an authenticated user id
     *
     * @return string
     */
    public function getUserId()
    {
        $user = new User();
        $user->personal_seed = 0;
        $user->application_id = static::TEST_APPLICATION_ID;
        $user->guid = SampleGenerator::createGuid();
        $user->identifier = "json";
        $user->is_deleted = false;
        $this->getDatabaseService()->saveToDatabase($user);

        $this->addVersion($user->guid, SampleGenerator::emptyGuid(), ContentType::USER);


        return $user->guid;
    }

    /**
     * returns an authenticated device id
     *
     * @param $userId
     * @param bool $isAuthenticated
     * @return string
     */
    public function getDeviceId($userId, $isAuthenticated = true)
    {
        $device = new Device();
        $device->guid = SampleGenerator::createGuid();
        $device->identifier = "json";
        $device->is_deleted = false;
        $device->is_authenticated = $isAuthenticated;
        $device->user_guid = $userId;
        $this->getDatabaseService()->saveToDatabase($device);

        $this->addVersion($device->guid, $device->guid, ContentType::DEVICE);

        return $device->guid;
    }

    /**
     * returns an authenticated device id
     *
     * @param $userId
     * @param $deviceId
     * @return string
     */
    public function getCollectionId($userId, $deviceId)
    {
        $collection = new Collection();
        $collection->guid = SampleGenerator::createGuid();
        $collection->identifier = "json";
        $collection->is_deleted = false;
        $collection->user_guid = $userId;
        $collection->device_guid = $deviceId;
        $this->getDatabaseService()->saveToDatabase($collection);

        $this->addVersion($collection->guid, $deviceId, ContentType::COLLECTION);

        $userCollection = new UserCollection();
        $userCollection->collection_guid = $collection->guid;
        $userCollection->create_date_time = time();
        $userCollection->user_guid = $userId;
        $this->getDatabaseService()->saveToDatabase($userCollection);

        return $collection->guid;
    }

    /**
     * add a content version for an entity
     *
     * @param $entityGuid
     * @param $deviceGuid
     * @param $contentType
     */
    private function addVersion($entityGuid, $deviceGuid, $contentType)
    {
        $contentVersion = new ContentVersion();
        $contentVersion->content = "{}";
        $contentVersion->content_type = $contentType;
        $contentVersion->create_date_time = time();
        $contentVersion->device_guid = $deviceGuid;
        $contentVersion->entity_guid = $entityGuid;
        $contentVersion->version_guid = SampleGenerator::createGuid();
        $this->getDatabaseService()->saveToDatabase($contentVersion);
    }
}