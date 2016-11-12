<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 20:48
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Controllers\Base\FrontendController;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginController extends FrontendController
{
    public function login(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/login", $args);
    }

    public function loginPost(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }

    public function register(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "login/register", $args);
    }

    public function registerPost(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }
}
