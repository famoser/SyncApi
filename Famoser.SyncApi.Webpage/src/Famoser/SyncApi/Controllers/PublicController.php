<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/06/2016
 * Time: 17:54
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\FrontendController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * the public controller displays the index page & other pages accessible to everyone
 * 
 * @package Famoser\SyncApi\Controllers
 */
class PublicController extends FrontendController
{
    /**
     * show basic infos about this application
     * 
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function index(Request $request, Response $response, $args)
    {
        return $this->renderTemplate($response, "public/index", $args);
    }
}
