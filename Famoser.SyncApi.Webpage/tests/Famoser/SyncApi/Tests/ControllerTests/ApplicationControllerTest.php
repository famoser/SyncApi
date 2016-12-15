<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 12:47
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Repositories\SettingsRepository;
use Famoser\SyncApi\Services\DatabaseService;
use Famoser\SyncApi\Tests\ControllerTests\Base\FrontendTestController;
use Famoser\SyncApi\Tests\TestHelpers\AssertHelper;

/**
 * test the application controller
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class ApplicationControllerTest extends FrontendTestController
{
    /**
     * tests if for all application nodes the login wall is up
     */
    public function testLoginAlwaysVerified()
    {
        $links = [
            "dashboard/",
            "dashboard/show/1",
            "dashboard/new",
            "dashboard/edit/1",
            "dashboard/settings/1",
            "dashboard/delete/1"
        ];

        foreach ($links as $link) {
            $this->loginWall($link);
        }

        $postLinks = [
            "dashboard/new",
            "dashboard/edit/1",
            "dashboard/settings/1",
            "dashboard/delete/1"
        ];
        foreach ($postLinks as $link) {
            $this->loginWall($link, "POST");
        }
    }

    /**
     * check if the corresponding relative link is behind the login wall
     *
     * @param $link
     * @param string $method
     */
    private function loginWall($link, $method = "GET")
    {
        if ($method == "POST") {
            $this->getTestHelper()->mockRequest($link, "data=true");
        } else if ($method == "GET") {
            $this->getTestHelper()->mockRequest($link);
        } else {
            static::fail("invalid method specified");
        }
        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForRedirectResponse($this, $response, 403, "login");
    }

    /**
     *  tests if all links return actual html, with no exceptions etc detectable
     */
    public function testDashboard()
    {
        $this->getTestHelper()->loginUser();
        $links = [
            "dashboard/",
            "dashboard/show/1",
            "dashboard/new",
            "dashboard/edit/1",
            "dashboard/settings/1",
            "dashboard/delete/1"
        ];

        foreach ($links as $link) {
            $this->getValidHtmlResponse($link);
        }
    }

    /**
     * check if the corresponding relative link is behind the login wall
     *
     * @param $link
     * @param string $method
     */
    private function getValidHtmlResponse($link, $method = "GET")
    {
        if ($method == "POST") {
            $this->getTestHelper()->mockRequest($link, "data=true");
        } else if ($method == "GET") {
            $this->getTestHelper()->mockRequest($link);
        } else {
            static::fail("invalid method specified");
        }
        $response = $this->getTestHelper()->getTestApp()->run();
        $responseStr = AssertHelper::checkForSuccessfulResponse($this, $response);
        static::assertNotEmpty($responseStr);
    }

    /**
     * test the create post action
     */
    public function testCreatePost()
    {
        $this->getTestHelper()->loginUser();
        $application = $this->getTestHelper()->getTestApplication();
        $this->getTestHelper()->mockRequest(
            "dashboard/new",
            [
                "name" => $application->name . "new",
                "description" => $application->description,
                "application_id" => $application->application_id . "new",
                "application_seed" => $application->application_seed
            ]
        );
        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForRedirectResponse($this, $response, 302, "dashboard/");

        $containerBase = new ContainerBase($this->getTestHelper()->getTestApp()->getContainer());
        $databaseService = $containerBase->getDatabaseService();
        $newApplication = $databaseService->getSingleFromDatabase(new Application(), null, null, "id DESC");

        static::assertEquals($application->name . "new", $newApplication->name);
        static::assertEquals($application->description, $newApplication->description);
        static::assertEquals($application->application_id . "new", $newApplication->application_id);
        static::assertEquals($application->application_seed, $newApplication->application_seed);
        static::assertEquals($application->admin_id, $this->getTestHelper()->getTestUser()->id);
        $threshold = 100;
        static::assertTrue(
            (time() - $threshold) < $application->release_date_time &&
            $application->release_date_time < (time() + $threshold),
            "release date time not in thresholds. expected difference: +-" .
            $threshold . " got: " .
            (time() - $application->release_date_time)
        );
    }

    /**
     * test the create post action
     */
    public function testSettingsPost()
    {
        $this->getTestHelper()->loginUser();
        $application = $this->getTestHelper()->getTestApplication();
        $containerBase = new ContainerBase($this->getTestHelper()->getTestApp()->getContainer());

        $settingsRepo = new SettingsRepository(
            $containerBase->getDatabaseService(),
            $this->getTestHelper()->getTestApplication()->id
        );

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

        $this->getTestHelper()->mockRequest(
            "dashboard/settings/" . $application->id,
            $newSettings
        );
        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForSuccessfulResponse($this, $response);


        //reconstruct settings repo & try again
        $settingsRepo = new SettingsRepository(
            $containerBase->getDatabaseService(),
            $this->getTestHelper()->getTestApplication()->id
        );
        $savedSettings = $settingsRepo->getAllSettings();
        foreach ($savedSettings as $item) {
            if (key_exists($item->key, $newSettings)) {
                static::assertEquals($newSettings[$item->key], $item->value);
            }
        }
    }
}