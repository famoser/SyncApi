<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 17:03
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\TestHelper;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;

/**
 * Class AuthorizationControllerTests
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class AuthorizationControllerTests extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $testHelper;

    public function setUp()
    {
        $this->testHelper =new TestHelper();
        $this->app = $this->testHelper->getTestApp();
    }

    public function testSync()
    {
        $this->testHelper->mockJsonRequest("")

        $response = $this->app->invoke();

        $this->assertContains('home', $response->getBody());
    }
}