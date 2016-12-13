<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:26
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Models\Entities\FrontendUser;
use Famoser\SyncApi\Services\SessionService;
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

        $userId = $this->getSessionService()->getValue(SessionService::FRONTEND_USER_ID, null);
        if ($userId === null) {
            return null;
        }

        $helper = $this->getDatabaseService();
        $this->frontendUser = $helper->getSingleFromDatabase(
            new FrontendUser(),
            "id = :id",
            ["id" => $userId]
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
        $this->getSessionService()->setValue(SessionService::FRONTEND_USER_ID, $user->id);
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
