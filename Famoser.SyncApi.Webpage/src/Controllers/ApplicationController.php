<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 21:00
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\BaseController;
use Famoser\SyncApi\Controllers\Base\FrontendController;
use Famoser\SyncApi\Exceptions\AccessDeniedException;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\FrontendUser;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Handlers\NotAllowed;
use Slim\Http\Request;
use Slim\Http\Response;

class ApplicationController extends FrontendController
{
    private function ensureHasAccess()
    {
        if (!$this->getFrontendUser())
            throw new AccessDeniedException();
    }

    private function getAuthorizedApplication($id)
    {
        $application = $this->getDatabaseHelper()->getSingleFromDatabase(new Application(), "id = :id", array("id" => $id));
        if ($this->getFrontendUser() && $this->getFrontendUser()->id == $application->admin_id)
            return;

        throw new AccessDeniedException();
    }

    public function index(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $applications = $this->getDatabaseHelper()->getFromDatabase(new Application(), "admin_id = :admin_id", array("admin_id", $this->getFrontendUser()->id));
        $args["applications"] = $applications;
        return $this->renderTemplate($response, "application/index", $args);
    }

    public function show(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/show", $args);
    }

    public function create(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        return $this->renderTemplate($response, "application/create", $args);
    }

    public function createPost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = new Application();
        $message = "";
        if ($this->writeFromPost($application, $message)) {
            $application->admin_id = $this->getFrontendUser()->id;
            $application->release_date_time = time();

            if ($this->getDatabaseHelper()->saveToDatabase($application))
                return $this->redirect($request, $response, "application_index");
            $args["message"] = "application could not be saved (database error)";
        } else
            $args["message"] = $message;
        return $this->renderTemplate($response, "application/create", $args);
    }

    public function edit(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/edit", $args);
    }

    public function editPost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        if ($this->writeFromPost($application, $message)) {
            if (!$this->getDatabaseHelper()->saveToDatabase($application))
                $args["message"] = "application could not be saved (database error)";
        } else
            $args["message"] = $message;
        return $this->renderTemplate($response, "application/edit", $args);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/delete", $args);
    }

    public function deletePost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        if (!$this->getDatabaseHelper()->deleteFromDatabase($application)) {
            $args["message"] = "application could not be saved (database error)";
            $args["application"] = $application;
            return $this->renderTemplate($response, "application/delete", $args);
        }
        return $this->redirect($request, $response, "application_index");
    }

    private function writeFromPost(Application $application, &$message)
    {
        $arr = $this->writePropertiesFromArray($_POST, $application, array("name", "description", "application_id", "application_seed"));
        if (count($arr) == 0) {
            //validate application seed
            if (!is_numeric($application->application_seed))
                $message = "the application seed has to be numeric";
            else {
                return true;
            }
        } else {
            $message = "the application could not be saved. please add the necessary information to ";
            if (count($arr) > 1) {
                $message .= implode(", ", array_splice($arr, -1));
                $message .= " and " . $arr[count($arr) - 1];
            } else {
                $message .= $arr[0];
            }
        }

        return false;
    }
}