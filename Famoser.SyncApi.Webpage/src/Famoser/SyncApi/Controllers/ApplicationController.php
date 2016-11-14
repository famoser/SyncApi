<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 21:00
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Controllers\Base\FrontendController;
use Famoser\SyncApi\Exceptions\AccessDeniedException;
use Famoser\SyncApi\Exceptions\FrontendException;
use Famoser\SyncApi\Models\Display\ApplicationStatistic;
use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\Entity;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Types\FrontendError;
use Slim\Http\Request;
use Slim\Http\Response;

class ApplicationController extends FrontendController
{
    private function ensureHasAccess()
    {
        if (!$this->getFrontendUser()) {
            throw new FrontendException(FrontendError::NOT_LOGGED_IN);
        }
    }

    /**
     * @param $id
     * @return Application
     * @throws AccessDeniedException
     */
    private function getAuthorizedApplication($id)
    {
        $application = $this->getDatabaseHelper()->getSingleFromDatabase(
            new Application(),
            "id = :id",
            ["id" => $id]
        );
        if ($this->getFrontendUser() && $this->getFrontendUser()->id == $application->admin_id) {
            return $application;
        }

        throw new AccessDeniedException();
    }

    public function index(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $applications = $this->getDatabaseHelper()->getFromDatabase(
            new Application(),
            "admin_id = :admin_id",
            ["admin_id" => $this->getFrontendUser()->id]
        );
        $args["applications"] = $applications;
        return $this->renderTemplate($response, "application/index", $args);
    }

    public function show(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        $args["stats"] = $this->getApplicationStats($application->application_id);
        return $this->renderTemplate($response, "application/show", $args);
    }

    /**
     * generate application statistic
     *
     * @param $applicationId
     * @return ApplicationStatistic
     */
    private function getApplicationStats($applicationId)
    {
        $appStats = new ApplicationStatistic();
        $users = $this->getDatabaseHelper()->getFromDatabase(
            new User(),
            "application_id = :application_id",
            ["application_id" => $applicationId],
            null,
            -1,
            "guid"
        );
        $appStats->usersCount = count($users);
        if ($appStats->usersCount == 0) {
            return $appStats;
        }

        $userGuids = [];
        foreach ($users as $user) {
            $userGuids[] = $user->guid;
        }

        $devices = $this->getDatabaseHelper()->getFromDatabase(
            new Device(),
            "user_guid IN (:" . array_keys($userGuids) . ")",
            $userGuids,
            null,
            -1,
            "guid"
        );
        $appStats->devicesCount = count($devices);
        if ($appStats->devicesCount == 0) {
            return $appStats;
        }

        $userCollections = $this->getDatabaseHelper()->getFromDatabase(
            new UserCollection(),
            "user_guid IN (:" . array_keys($userGuids) . ")",
            $userGuids,
            null,
            -1,
            "collection_guid"
        );
        $collectionGuids = [];
        foreach ($userCollections as $userCollection) {
            $collectionGuids[$userCollection->collection_guid] = true;
        }
        $collectionGuids = array_keys($collectionGuids);
        $appStats->collectionsCount = count($collectionGuids);
        if ($appStats->collectionsCount == 0) {
            return $appStats;
        }

        $appStats->itemsCount = $this->getDatabaseHelper()->countFromDatabase(
            new Entity(),
            "collection_guid IN (:" . array_keys($collectionGuids) . ")",
            $collectionGuids
        );
        return $appStats;
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
        var_dump($request->getParsedBody());
        if ($this->writeFromPost($application, $request->getParsedBody(), $message, true)) {
            $application->admin_id = $this->getFrontendUser()->id;
            $application->release_date_time = time();

            $existing = $this->getDatabaseHelper()->getSingleFromDatabase(
                new Application(),
                "application_id = :application_id",
                ["application_id" => $application->application_id]
            );
            if ($existing != null) {
                $args["message"] = "application with this id already exists";
            } elseif ($this->getDatabaseHelper()->saveToDatabase($application)) {
                return $this->redirect($request, $response, "application_index");
            } else {
                $args["message"] = "application could not be saved (database error)";
            }
        } else {
            $args["message"] = $message;
        }
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
        if ($this->writeFromPost($application, $request->getParsedBody(), $message)) {
            if (!$this->getDatabaseHelper()->saveToDatabase($application)) {
                $args["message"] = "application could not be saved (database error)";
            }
        } else {
            $args["message"] = $message;
        }
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/edit", $args);
    }

    public function remove(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/delete", $args);
    }

    public function removePost(Request $request, Response $response, $args)
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

    private function writeFromPost(Application $application, array $source, &$message, $createAction = false)
    {
        $propArray = ["name", "description"];
        if ($createAction) {
            $propArray = ["name", "description", "application_id", "application_seed"];
        }
        $arr = $this->writePropertiesFromArray($source, $application, $propArray);
        if (count($arr) == 0) {
            //validate application seed
            if (is_numeric($application->application_seed)) {
                return true;
            }
            $message = "the application seed has to be numeric";
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
