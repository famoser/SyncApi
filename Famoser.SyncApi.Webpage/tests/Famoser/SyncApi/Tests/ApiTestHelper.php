<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 19:51
 */

namespace Famoser\SyncApi\Tests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\SyncApiApp;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Environment;

/**
 * helps preparing the test cases
 *
 * @package Famoser\SyncApi\Tests
 */
class ApiTestHelper extends ContainerBase
{
    const TEST_APPLICATION_ID = "test_app";
    const TEST_APPLICATION_SEED = 0;

    /* @var SyncApiApp $testApp */
    private $testApp;
    /* @var array $config */
    private $config;

    /**
     * TestHelper constructor.
     */
    public function __construct()
    {
        //create config array
        $this->config = $this->constructConfig();

        //create test app
        $this->testApp = new SyncApiApp($this->config);

        //use container to initialize parent
        parent::__construct($this->testApp->getContainer());

        //prepare environment
        $this->prepareDatabase();
    }

    /**
     * resets application to prepare for new request, but does not reset the database
     */
    public function resetApplication()
    {
        //clean output buffer
        ob_end_clean();
        //start again so phpunit does not throw risky exceptions (that motherf***er)
        ob_start();

        //dispose database service (free up database connection)
        $this->getDatabaseService()->dispose();

        //create test app
        $this->testApp = new SyncApiApp($this->config);

        //use container to initialize parent
        parent::__construct($this->testApp->getContainer());
    }

    /**
     * construct the configuration
     *
     * @return array
     */
    private function constructConfig()
    {
        $ds = DIRECTORY_SEPARATOR;
        $oneUp = ".." . $ds;
        $basePath = realpath(__DIR__ . "/" . $oneUp . $oneUp . $oneUp . $oneUp) . $ds;
        $config =
            [
                'displayErrorDetails' => true,
                'debug_mode' => true,
                'api_modulo' => 10000019,
                'db_path' => $basePath . "app" . $ds . "data_test_" . uniqid() . ".sqlite",
                'db_template_path' => $basePath . "app" . $ds . "data_test_template.sqlite",
                'file_path' => $basePath . "app" . $ds . "files",
                'cache_path' => $basePath . "app" . $ds . "cache",
                'log_path' => $basePath . "app" . $ds . "logs",
                'template_path' => $basePath . "app" . $ds . "templates",
                'public_path' => $basePath . "src" . $ds . "public",
                'src_path' => $basePath . "src",
            ];

        return $config;
    }

    /**
     * get an array of instances of all the classes in this exact namespace
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param $nameSpace
     * @return array
     */
    public function getClassInstancesInNamespace(\PHPUnit_Framework_TestCase $testCase, $nameSpace)
    {
        $containerBase = new ContainerBase($this->getTestApp()->getContainer());
        $srcPath = $containerBase->getSettingsArray()["src_path"];
        $filePath = str_replace("\\", DIRECTORY_SEPARATOR, $nameSpace);
        $res = [];
        foreach (glob($srcPath . DIRECTORY_SEPARATOR . $filePath . DIRECTORY_SEPARATOR . "*.php") as $filename) {
            $className = $nameSpace . "\\" . substr($filename, strrpos($filename, DIRECTORY_SEPARATOR) + 1, -4);
            $res[] = new $className();
        }
        $testCase::assertTrue(count($res) > 0);
        foreach ($res as $obj) {
            $testCase::assertTrue(is_object($obj));
        }
        return $res;
    }

    /**
     * returns the test application app
     *
     * @return SyncApiApp
     */
    public function getTestApp()
    {
        return $this->testApp;
    }

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
     * cleans the environment, including database
     */
    public function cleanEnvironment()
    {
        $this->getDatabaseService()->dispose();
        //delete db if exists
        if (is_file($this->config ["db_path"])) {
            unlink($this->config ["db_path"]);
        }
    }

    /**
     * prepares the environment
     */
    private function prepareDatabase()
    {
        //create test application
        $application = new Application();
        $application->application_id = static::TEST_APPLICATION_ID;
        $application->application_seed = static::TEST_APPLICATION_SEED;
        $application->description = "a test application created while running tests";
        $application->name = "Test Application";
        $application->release_date_time = time() - 1;
        $this->getDatabaseService()->saveToDatabase($application);
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

        return $user->guid;
    }

    /**
     * returns an authenticated device id
     *
     * @param $userId
     * @return string
     */
    public function getDeviceId($userId)
    {
        $device = new Device();
        $device->guid = SampleGenerator::createGuid();
        $device->identifier = "json";
        $device->is_deleted = false;
        $device->is_authenticated = true;
        $device->user_guid = $userId;
        $this->getDatabaseService()->saveToDatabase($device);

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

        $userCollection = new UserCollection();
        $userCollection->collection_guid = $collection->guid;
        $userCollection->create_date_time = time();
        $userCollection->user_guid = $userId;
        $this->getDatabaseService()->saveToDatabase($userCollection);

        return $collection->guid;
    }
}