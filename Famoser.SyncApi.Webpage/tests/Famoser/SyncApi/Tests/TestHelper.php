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
use Famoser\SyncApi\SyncApiApp;
use Slim\Http\Environment;

/**
 * helps preparing the test cases
 *
 * @package Famoser\SyncApi\Tests
 */
class TestHelper extends ContainerBase
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
        //clean environment
        $this->cleanEnvironment();

        //create test app
        $this->testApp = new SyncApiApp($this->config);

        //use container to initialize parent
        parent::__construct($this->testApp->getContainer());

        //prepare environment
        $this->prepareEnvironment();
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
                'db_path' => $basePath . "app" . $ds . "data_test.sqlite",
                'db_template_path' => $basePath . "app" . $ds . "data_test_template.sqlite",
                'file_path' => $basePath . "app" . $ds . "files",
                'cache_path' => $basePath . "app" . $ds . "cache",
                'log_path' => $basePath . "app" . $ds . "logs",
                'template_path' => $basePath . "app" . $ds . "templates",
                'public_path' => $basePath . "src" . $ds . "public"
            ];

        return $config;
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

    /**
     * mock a json POST request
     * call app->run afterwards
     *
     * @param BaseRequest $request
     * @param $relativeLink
     * @param SyncApiApp $app
     * @internal param $json
     */
    public function mockApiRequest(BaseRequest $request, $relativeLink, SyncApiApp $app)
    {
        $json = json_encode($request, JSON_PRETTY_PRINT);
        $app->overrideEnvironment(
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
        //delete db if exists
        if (is_file($this->config ["db_path"])) {
            unlink($this->config ["db_path"]);
        }
    }

    /**
     * prepares the environment
     */
    public function prepareEnvironment()
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
}