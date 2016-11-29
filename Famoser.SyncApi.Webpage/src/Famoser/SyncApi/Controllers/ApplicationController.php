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
use Famoser\SyncApi\Repositories\SettingsRepository;
use Famoser\SyncApi\Types\FrontendError;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * a frontend controller which allows access to the applications
 *
 * Class ApplicationController
 * @package Famoser\SyncApi\Controllers
 */
class ApplicationController extends FrontendController
{
    private function ensureHasAccess()
    {
        if (!$this->getFrontendUser()) {
            throw new FrontendException(FrontendError::NOT_LOGGED_IN);
        }
    }

    /**
     * @param $entityId
     * @return Application
     * @throws AccessDeniedException
     */
    private function getAuthorizedApplication($entityId)
    {
        $application = $this->getDatabaseHelper()->getSingleFromDatabase(
            new Application(),
            "id = :id",
            ["id" => $entityId]
        );
        if ($this->getFrontendUser() && $this->getFrontendUser()->id == $application->admin_id) {
            return $application;
        }

        throw new AccessDeniedException();
    }

    /**
     * display all applications
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws FrontendException
     */
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

    /**
     * show a single application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function show(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        $args["stats"] = $this->getApplicationStats($application->application_id);
        return $this->renderTemplate($response, "application/show", $args);
    }

    /**
     * show the settings for an application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function settings(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $settingsRepo = new SettingsRepository($this->getDatabaseHelper(), $application->application_id);
        $args["settings"] = $settingsRepo->getAllSettings();
        return $this->renderTemplate($response, "application/settings", $args);
    }

    /**
     * process the post request for an application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function settingsPost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $settingsRepo = new SettingsRepository($this->getDatabaseHelper(), $application->application_id);
        $settingsRepo->setSettings($request->getParsedBody());
        $args["settings"] = $settingsRepo->getAllSettings();
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

    /**
     * show a form to display a new application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws FrontendException
     */
    public function create(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        return $this->renderTemplate($response, "application/create", $args);
    }

    /**
     * the post request for creating a new application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed|static
     * @throws FrontendException
     */
    public function createPost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = new Application();
        $message = "";
        var_dump($request->getParsedBody());
        if ($this->writeFromPost(
            $application,
            $request->getParsedBody(),
            $message,
            ["name", "description", "application_id", "application_seed"]
        )
        ) {
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

    /**
     * show a form to edit an application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function edit(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/edit", $args);
    }

    /**
     * the post request from the edit form
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function editPost(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        if ($this->writeFromPost($application, $request->getParsedBody(), $message, ["name", "description"])) {
            if (!$this->getDatabaseHelper()->saveToDatabase($application)) {
                $args["message"] = "application could not be saved (database error)";
            }
        } else {
            $args["message"] = $message;
        }
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/edit", $args);
    }

    /**
     * show a form to remove an application
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     * @throws AccessDeniedException
     * @throws FrontendException
     */
    public function remove(Request $request, Response $response, $args)
    {
        $this->ensureHasAccess();
        $application = $this->getAuthorizedApplication($args["id"]);
        $args["application"] = $application;
        return $this->renderTemplate($response, "application/delete", $args);
    }

    /**
     * the post request to remove a form lands here
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed|static
     * @throws AccessDeniedException
     * @throws FrontendException
     */
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

    /**
     * write all specified application properties
     *
     * @param Application $application
     * @param array $source
     * @param $message
     * @param array $propArray
     * @return bool
     */
    private function writeFromPost(Application $application, array $source, &$message, array $propArray)
    {
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