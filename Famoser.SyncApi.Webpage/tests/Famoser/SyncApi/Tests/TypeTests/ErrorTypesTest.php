<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12.12.2016
 * Time: 13:00
 */

namespace Famoser\SyncApi\Tests\TypeTests;


use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\FrontendError;
use Famoser\SyncApi\Types\ServerError;
use ReflectionClass;

/**
 * tests the error types
 *
 * @package Famoser\SyncApi\Tests\TypeTests
 */
class ErrorTypesTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_NAMESPACE = "Famoser\\SyncApi\\Types\\";

    /**
     * tests that all error descriptions for the different api errors are unqiue
     */
    public function testAllDifferentApiErrorDescriptions()
    {
        $reflection = new ReflectionClass(static::ERROR_NAMESPACE . "ApiError");
        $messages = [];
        //add default error description
        $messages[ApiError::toString(-1)] = true;
        //code must be in default error description
        static::assertContains("-1", ApiError::toString(-1));
        foreach ($reflection->getConstants() as $constant) {
            $message = ApiError::toString($constant);
            static::assertArrayNotHasKey($message, $messages);
            $tableNames[$message] = true;
        }
    }

    /**
     * tests that all error descriptions for the different frontend errors are unqiue
     */
    public function testAllDifferentFrontendErrorDescriptions()
    {
        $reflection = new ReflectionClass(static::ERROR_NAMESPACE . "FrontendError");
        $messages = [];
        //add default error description
        $messages[FrontendError::toString(-1)] = true;
        //code must be in default error description
        static::assertContains("-1", FrontendError::toString(-1));
        foreach ($reflection->getConstants() as $constant) {
            $message = FrontendError::toString($constant);
            static::assertArrayNotHasKey($message, $messages);
            $tableNames[$message] = true;
        }
    }

    /**
     * tests that all error descriptions for the different frontend errors are unqiue
     */
    public function testAllDifferentServerErrorDescriptions()
    {
        $reflection = new ReflectionClass(static::ERROR_NAMESPACE . "ServerError");
        $messages = [];
        //add default error description
        $messages[ServerError::toString(-1)] = true;
        //code must be in default error description
        static::assertContains("-1", ServerError::toString(-1));
        foreach ($reflection->getConstants() as $constant) {
            $message = ServerError::toString($constant);
            static::assertArrayNotHasKey($message, $messages);
            $tableNames[$message] = true;
        }
    }
}