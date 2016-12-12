<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 12.12.2016
 * Time: 13:19
 */

namespace Famoser\SyncApi\Tests\TypeTests;


use Famoser\SyncApi\Types\SettingKeys;
use ReflectionClass;

/**
 * Class SettingKeysTest
 * @package Famoser\SyncApi\Tests\TypeTests
 */
class SettingKeysTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_NAMESPACE = "Famoser\\SyncApi\\Types\\";

    /**
     * tests that all error descriptions for the different api errors are unqiue
     */
    public function testAllDifferentDescriptions()
    {
        $reflection = new ReflectionClass(static::ERROR_NAMESPACE . "SettingKeys");
        $messages = [];
        //add default error description
        $messages[SettingKeys::getSettingDescription(-1)] = true;
        //code must be in default description
        static::assertContains("-1", SettingKeys::getSettingDescription(-1));
        foreach ($reflection->getConstants() as $constant) {
            $message = SettingKeys::getSettingDescription($constant);
            static::assertArrayNotHasKey($message, $messages);
            $tableNames[$message] = true;
        }
    }

    /**
     * tests that all error descriptions for the different api errors are unqiue
     */
    public function testDefaultValuesAndValidValueMatch()
    {
        $reflection = new ReflectionClass(static::ERROR_NAMESPACE . "SettingKeys");
        //code must be in default description
        static::assertContains("-1", SettingKeys::getDefaultValue(-1));
        foreach ($reflection->getConstants() as $constant) {
            $message = SettingKeys::getDefaultValue($constant);
            static::assertTrue(SettingKeys::isValidValue($constant,$message));
        }
    }
}