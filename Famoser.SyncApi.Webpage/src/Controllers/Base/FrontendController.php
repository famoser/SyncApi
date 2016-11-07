<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:26
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Models\Entities\FrontendUser;
use Slim\Http\Response;

class FrontendController extends BaseController
{

    private $frontendUser;

    /**
     * @return FrontendUser|null
     */
    protected function getFrontendUser()
    {
        if ($this->frontendUser != null)
            return $this->frontendUser;

        if (!isset($_SESSION["admin_id"]))
            return null;

        $helper = $this->getDatabaseHelper();
        $this->frontendUser = $helper->getSingleFromDatabase(new FrontendUser(), "id = :id", array("id" => $_SESSION["admin_id"]));
        return $this->frontendUser;
    }


    /**
     * @param Response $response
     * @param $path
     * @param $args
     * @return mixed
     */
    protected function renderTemplate(Response $response, $path, $args)
    {
        return $this->container->get("view")->render($response, $path . ".html.twig", $args);
    }
}