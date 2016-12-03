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

/**
 * a frontend controller displays information in the web application.
 * @package Famoser\SyncApi\Controllers\Base
 */
class FrontendController extends BaseController
{

    private $frontendUser;

    /**
     * get the frontend user
     *
     * @return FrontendUser|null
     */
    protected function getFrontendUser()
    {
        if ($this->frontendUser != null) {
            return $this->frontendUser;
        }

        if (!isset($_SESSION["frontend_user_id"])) {
            return null;
        }

        $helper = $this->getDatabaseService();
        $this->frontendUser = $helper->getSingleFromDatabase(
            new FrontendUser(), 
            "id = :id", 
            ["id" => $_SESSION["frontend_user_id"]]
        );
        return $this->frontendUser;
    }

    /**
     * set the frontend user
     *
     * @param FrontendUser $user
     */
    protected function setFrontendUser(FrontendUser $user)
    {
        $_SESSION["frontend_user_id"] = $user->id;
    }


    /**
     * @param Response $response
     * @param $path
     * @param $args
     * @return mixed
     */
    protected function renderTemplate(Response $response, $path, $args)
    {
        return $this->getView()->render($response, $path . ".html.twig", $args);
    }
}
