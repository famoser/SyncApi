<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/06/2016
 * Time: 17:54
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Controllers\Base\FrontendController;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicController extends FrontendController
{
    public function index(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "index", $args);
    }

    public function indexAsJson(Request $request, Response $response, $args)
    {
        return $response->withJson($args);
    }
}