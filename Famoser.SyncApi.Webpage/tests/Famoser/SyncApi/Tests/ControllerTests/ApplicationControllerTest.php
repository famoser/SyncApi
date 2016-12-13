<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13.12.2016
 * Time: 12:47
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


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
        $responseStr = AssertHelper::checkForFailedResponse($this, $response, 403);
        static::assertContains("login", $response->getHeaderLine("location"));
        static::assertEmpty($responseStr);
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
}