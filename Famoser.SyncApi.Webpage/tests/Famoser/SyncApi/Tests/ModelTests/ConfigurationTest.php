<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 10.12.2016
 * Time: 23:48
 */

namespace Famoser\SyncApi\Tests\ModelTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;
use Famoser\SyncApi\Tests\ApiTestHelper;

/**
 * tests the configuration of the models
 *
 * @package Famoser\SyncApi\Tests\ModelTests
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testTableNamesUnique()
    {
        $testHelper = new ApiTestHelper();
        $classes = $testHelper->getClassInstancesInNamespace($this, "Famoser\\SyncApi\\Models\\Entities");

        $tableNames = [];
        /* @var BaseEntity[] $classes */
        foreach ($classes as $class) {
            static::assertArrayNotHasKey($class->getTableName(), $tableNames);
            $tableNames[$class->getTableName()] = true;
        }
    }
}