<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 19:51
 */

namespace Famoser\SyncApi\Tests;


use Famoser\SyncApi\SyncApiApp;
use Slim\Http\Environment;

/**
 * helps preparing the test cases
 *
 * @package Famoser\SyncApi\Tests
 */
class TestHelper
{
    /**
     * construct the configuration
     *
     * @return array
     */
    private function constructConfig()
    {
        $ds = DIRECTORY_SEPARATOR;
        $oneUp = ".." . $ds;
        $basePath = realpath($oneUp . $oneUp . $oneUp . $oneUp . $oneUp) . $ds;
        $config =
            [
                'displayErrorDetails' => true,
                'debug_mode' => true,
                'api_modulo' => 10000019,
                'db_path' => $basePath . "app" . $ds . "data_test.sqlite",
                'db_template_path' => $basePath . "app" . $ds . "data_template.sqlite",
                'file_path' => $basePath . "app" . $ds . "files",
                'cache_path' => $basePath . "app" . $ds . "cache",
                'log_path' => $basePath . "app" . $ds . "logs",
                'template_path' => $basePath . "app" . $ds . "templates",
                'public_path' => $basePath . "src" . $ds . "public"
            ];

        return $config;
    }

    /**
     * create a test application app
     *
     * @param bool $cleanDatabase
     * @return SyncApiApp
     */
    public function getTestApp($cleanDatabase = true, $prefilData = true)
    {
        $config = $this->constructConfig();

        if ($cleanDatabase) {
            $this->tryCleanDatabase($config);
        }

        return new SyncApiApp($config);
    }

    /**
     * mock a json POST request
     * call app->run afterwards
     *
     * @param $json
     * @param $relativeLink
     * @param SyncApiApp $app
     */
    public function mockApiRequest($json, $relativeLink, SyncApiApp $app)
    {
        // does not work as expected
        $tmp_handle = fopen('php://temp', 'w+');
        fwrite($tmp_handle, $json);
        rewind($tmp_handle);
        fclose($tmp_handle);

        $app->overrideEnvironment(
            Environment::mock(
                [
                    'REQUEST_METHOD' => 'POST',
                    'REQUEST_URI' => '/1.0/' . $relativeLink,
                    'slim.input' => $json,
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
        $config = $this->constructConfig();
        $this->tryCleanDatabase($config);
    }

    /**
     * tries to delete the database
     *
     * @param $config
     */
    private function tryCleanDatabase($config)
    {
        if (is_file($config["db_path"])) {
            unlink($config["db_path"]);
        }
    }
}