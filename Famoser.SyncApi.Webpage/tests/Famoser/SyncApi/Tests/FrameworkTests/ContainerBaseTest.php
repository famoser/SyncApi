<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12.12.2016
 * Time: 13:33
 */

namespace Famoser\SyncApi\Tests\FrameworkTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Services\Interfaces\DatabaseServiceInterface;
use Famoser\SyncApi\Services\Interfaces\LoggingServiceInterface;
use Famoser\SyncApi\Services\Interfaces\RequestServiceInterface;
use Famoser\SyncApi\Tests\ApiTestHelper;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use Slim\Views\Twig;

/**
 * test the container base
 * @package Famoser\SyncApi\Tests\FrameworkTests
 */
class ContainerBaseTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertiesCorrect()
    {
        $testHelper = new ApiTestHelper();
        $app = $testHelper->getTestApp();
        $container = new ContainerBase($app->getContainer());

        static::assertInstanceOf(RouterInterface::class, $container->getRouter());
        static::assertInstanceOf(DatabaseServiceInterface::class, $container->getDatabaseService());
        static::assertInstanceOf(LoggingServiceInterface::class, $container->getLoggingService());
        static::assertInstanceOf(RequestServiceInterface::class, $container->getRequestService());
        static::assertTrue(count($container->getSettingsArray()) > 0);
        static::assertInstanceOf(Twig::class, $container->getView());

        //6 methods tested + __construct
        $expectedMethodCount = 7;
        $actualMethodCount = count(get_class_methods(ContainerBase::class));
        static::assertTrue(
            $actualMethodCount == $expectedMethodCount,
            "expected " . $expectedMethodCount . " methods, got " . $actualMethodCount
        );
    }
}