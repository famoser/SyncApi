<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 16.12.2016
 * Time: 12:15
 */

namespace Famoser\SyncApi\Tests\ServiceTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Entities\FrontendUser;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Tests\ServiceTests\Base\BaseTestService;
use Famoser\SyncApi\Tests\TestHelpers\FrontendTestHelper;

/**
 * tests the database service
 * the tests in here are very basic, as it can be assumed all methods from DS already word due
 * a) simplicity or
 * b) heavy manuela testing or
 * c) heavy usage by the whole application
 *
 * @package Famoser\SyncApi\Tests\ServiceTests
 */
class DatabaseServiceTest extends BaseTestService
{
     /**
     * tests the get by id method
     */
    public function testGetWithIn()
    {
        $databaseService = $this->getDatabaseService();
        $user = $this->testHelper->getTestUser();
        $res = $databaseService->getWithInFromDatabase(new FrontendUser(), "id", [1, 2, $user->id]);
        static::assertTrue(count($res) == 1);

        $res = $databaseService->getWithInFromDatabase(new FrontendUser(), "id", [100]);
        static::assertTrue(count($res) == 0);
    }

    /**
     * tests the getById method
     */
    public function testGetById()
    {
        $databaseService = $this->getDatabaseService();
        $user = $this->testHelper->getTestUser();
        $res = $databaseService->getSingleByIdFromDatabase(new FrontendUser(), $user->id);
        static::assertTrue(count($res) == 1);

        $res = $databaseService->getSingleByIdFromDatabase(new FrontendUser(), $user->id + 1);
        static::assertTrue(count($res) == 0);
    }
}