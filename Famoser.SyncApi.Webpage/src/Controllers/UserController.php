<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:02
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\ApiRequestController;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController extends ApiRequestController
{
    public function auth(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }
}
