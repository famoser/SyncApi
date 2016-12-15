<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.12.2016
 * Time: 10:37
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Tests\ControllerTests\Base\FrontendTestController;
use Famoser\SyncApi\Tests\TestHelpers\AssertHelper;

/**
 * tests the login controller
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class LoginControllerTest extends FrontendTestController
{
    public function testLogin()
    {

    }


    /**
     *  tests if all links return actual html, with no exceptions etc detectable
     */
    public function testAllRendering()
    {
        $this->getTestHelper()->loginUser();
        $links = [
            "login",
            "register",
            "forgot",
            "recover"
        ];

        foreach ($links as $link) {
            $this->getValidHtmlResponse($link);
            $this->getValidHtmlResponse($link, "POST");
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