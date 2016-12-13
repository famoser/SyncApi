<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 10:44
 */

namespace Famoser\SyncApi\Tests\RepositoryTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Repositories\SettingsRepository;
use Famoser\SyncApi\Tests\TestHelpers\ApiTestHelper;
use Famoser\SyncApi\Tests\TypeTests\SettingKeysTest;
use ReflectionClass;

/**
 * tests the SettingsRepository
 * @package Famoser\SyncApi\Tests\RepositoryTests
 */
class SettingsRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /* @var ContainerBase $baseContainerCache */
    private $baseContainerCache;

    /**
     * @return SettingsRepository
     */
    private function getSettingsRepository()
    {
        if ($this->baseContainerCache == null) {
            $testHelper = new ApiTestHelper();
            $app = $testHelper->getTestApp();
            $this->baseContainerCache = new ContainerBase($app->getContainer());
        }
        return new SettingsRepository(
            $this->baseContainerCache->getDatabaseService(),
            ApiTestHelper::TEST_APPLICATION_ID
        );
    }

    public function testAllSettingsReturned()
    {
        $settingsRepo = $this->getSettingsRepository();
        $reflection = new ReflectionClass(SettingKeysTest::ERROR_NAMESPACE . "SettingKeys");
        static::assertEquals(count($reflection->getConstants()), count($settingsRepo->getAllSettings()));
    }

    public function testCanChangeSettingsReturned()
    {
        $settingsRepo = $this->getSettingsRepository();
        $originSetting = $settingsRepo->getAllSettings();
        $newSettings = [];
        foreach ($originSetting as $item) {
            if (is_numeric($item->value)) {
                $newSettings[$item->key] = $item->value + 1;
            } else if (is_string($item->value)) {
                if ($item->value == "true") {
                    $newSettings[$item->key] = "false";
                } else if ($item->value == "false") {
                    $newSettings[$item->key] = "true";
                } else {
                    $newSettings[$item->key] = $item->value . "-new";
                }
            }
        }
        $settingsRepo->setSettings($newSettings);

        //get again, see if it persisted
        $savedSettings = $settingsRepo->getAllSettings();
        foreach ($savedSettings as $item) {
            if (key_exists($item->key, $newSettings)) {
                static::assertEquals($newSettings[$item->key], $item->value);
            }
        }

        //reconstruct settings repo & try again
        $settingsRepo = $this->getSettingsRepository();
        $savedSettings = $settingsRepo->getAllSettings();
        foreach ($savedSettings as $item) {
            if (key_exists($item->key, $newSettings)) {
                static::assertEquals($newSettings[$item->key], $item->value);
            }
        }
    }
}