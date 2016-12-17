<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 12:55
 */

namespace Famoser\SyncApi\Tests\TestHelpers\Base;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\TestHelpers\TestApp\TestSyncApiApp;

/**
 * helps to test a Slim application
 * @package Famoser\SyncApi\Tests\TestHelpers\Base
 */
abstract class BaseTestHelper extends ContainerBase
{

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
        $this->testApp = new TestSyncApiApp($this->config);

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
        $this->testApp = new TestSyncApiApp($this->config);

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
        $basePath = realpath(__DIR__ . "/" . $oneUp . $oneUp . $oneUp . $oneUp . $oneUp . $oneUp) . $ds;
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
                'mail' => ['type' => 'mock']
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
     * prepare the database if needed
     */
    abstract protected function prepareDatabase();
}