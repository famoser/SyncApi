<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:03
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

class CollectionController extends BaseController
{
    public function sync(Request $request, Response $response, $args)
    {
        throw new \Exception("not implemented");
    }
}