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
     * create a test application app
     *
     * @param bool $cleanDatabase
     * @return SyncApiApp
     */
    public function getTestApp($cleanDatabase = true)
    {
        $indentation = "../../../../";
        $config =
            [
                'settings' =>
                    [
                        'displayErrorDetails' => true,
                        'debug_mode' => true,
                        'api_modulo' => 10000019,
                        'db_path' => realpath($indentation . "app/data_test.sqlite"),
                        'db_template_path' => realpath($indentation . "app/data_template.sqlite"),
                        'file_path' => realpath($indentation . "app/files"),
                        'cache_path' => realpath($indentation . "app/cache"),
                        'log_path' => realpath($indentation . "app/logs"),
                        'template_path' => realpath($indentation . "app/templates"),
                        'public_path' => realpath($indentation . "src/public")
                    ]
            ];

        if ($cleanDatabase && is_file($config["db_path"])) {
            unlink($config["db_path"]);
        }

        return new SyncApiApp($config);
    }

    /**
     * mock a json POST request
     * call app->run afterwards
     *
     * @param $json
     * @param $link
     */
    public function mockApiRequest($json, $link)
    {
        Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/1.0/' . $link,
            'QUERY_STRING' => $json,
            'SERVER_NAME' => 'localhost',
            'CONTENT_TYPE' => 'application/json;charset=utf8',
            'CONTENT_LENGTH' => 15
        ]);
    }
}