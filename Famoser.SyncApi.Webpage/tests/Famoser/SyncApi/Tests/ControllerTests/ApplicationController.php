<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.11.2016
 * Time: 21:18
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Slim\Http\Environment;

/**
 * tests the application controller
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class ApplicationController extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        $mockEv = Environment::mock();
        
    }

}