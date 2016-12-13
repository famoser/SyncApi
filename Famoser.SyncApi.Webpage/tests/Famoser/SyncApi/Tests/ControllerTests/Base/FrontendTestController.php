<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 12:48
 */

namespace Famoser\SyncApi\Tests\ControllerTests\Base;


use Famoser\SyncApi\Tests\TestHelpers\FrontendTestHelper;

/**
 * test frontend nodes
 * @package Famoser\SyncApi\Tests\ControllerTests\Base
 */
class FrontendTestController extends \PHPUnit_Framework_TestCase
{
    /* @var FrontendTestHelper $testHelper */
    private $testHelper;

    /**
     * FrontendTestController constructor.
     */
    public function __construct()
    {
        $this->testHelper = new FrontendTestHelper();
    }

    /**
     * @return FrontendTestHelper
     */
    protected function getTestHelper()
    {
        return $this->testHelper;
    }
}