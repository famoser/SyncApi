<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.12.2016
 * Time: 10:37
 */

namespace Famoser\SyncApi\Tests\ControllerTests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Entities\FrontendUser;
use Famoser\SyncApi\Tests\ControllerTests\Base\FrontendTestController;
use Famoser\SyncApi\Tests\TestHelpers\AssertHelper;

/**
 * tests the login controller
 *
 * @package Famoser\SyncApi\Tests\ControllerTests
 */
class LoginControllerTest extends FrontendTestController
{
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
     * check if the corresponding relative link responds html
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
     * tests the register function
     */
    public function testRegisterPost()
    {
        $usr = new FrontendUser();
        $usr->email = "emil@mymail.com";
        $usr->password = "password";
        $usr->reset_key = "reset_key";
        $usr->username = "usrname";
        $this->getTestHelper()->mockRequest("register",
            [
                "email" => $usr->email,
                "username" => $usr->username,
                "password" => $usr->password,
                "password2" => $usr->password
            ]);

        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForRedirectResponse($this, $response, 302, "login");

        $containerBase = new ContainerBase($this->getTestHelper()->getTestApp()->getContainer());
        $savedUser = $containerBase->getDatabaseService()->getSingleFromDatabase(
            new FrontendUser(),
            "email = :email AND username = :username",
            ["email" => $usr->email, "username" => $usr->username]
        );

        static::assertNotNull($savedUser);
        static::assertTrue(password_verify($usr->password, $savedUser->password));
    }

    /**
     * tests the register function
     */
    public function testDoubleRegisterPost()
    {
        $usr = $this->getTestHelper()->getTestUser();
        $this->getTestHelper()->mockRequest("register",
            [
                "email" => $usr->email,
                "username" => $usr->username,
                "password" => $usr->password,
                "password2" => $usr->password
            ]);

        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForSuccessfulResponse($this, $response);
    }

    /**
     * tests the register function
     */
    public function testPasswordTypoRegisterPost()
    {
        $usr = $this->getTestHelper()->getTestUser();
        $this->getTestHelper()->mockRequest("register",
            [
                "email" => $usr->email . "o",
                "username" => $usr->username . "o",
                "password" => $usr->password,
                "password2" => $usr->password . "o"
            ]);

        $response = $this->getTestHelper()->getTestApp()->run();
        AssertHelper::checkForSuccessfulResponse($this, $response);
    }
}