<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 09.12.2016
 * Time: 08:45
 */

namespace Famoser\SyncApi\Tests\ControllerTests\Base;


use Famoser\SyncApi\Tests\TestHelper;

/**
 * a base class for all api tests
 *
 * @package Famoser\SyncApi\Tests\ControllerTests\Base
 */
class ApiTestController extends \PHPUnit_Framework_TestCase
{
    /* @var TestHelper $testHelper */
    protected $testHelper;

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
}