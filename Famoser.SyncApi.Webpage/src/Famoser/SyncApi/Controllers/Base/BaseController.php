<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:23
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Repositories\SettingsRepository;
use Famoser\SyncApi\Services\Interfaces\LoggerInterface;
use Famoser\SyncApi\Services\Interfaces\RequestServiceInterface;
use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

/**
 * the base controller which provides access to the environment
 *
 * Class BaseController
 * @package Famoser\SyncApi\Controllers\Base
 */
class BaseController
{
    /* @var ContainerInterface $container */
    protected $container;

    /**
     * BaseController constructor.
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->container = $ci;
    }

    private $databaseHelper;

    /**
     * get database helper, used for database access
     *
     * @return DatabaseHelper
     */
    protected function getDatabaseHelper()
    {
        if ($this->databaseHelper == null) {
            $this->databaseHelper = new DatabaseHelper($this->container);
        }
        return $this->databaseHelper;
    }

    /**
     * get SettingsRepository for the specified application
     *
     * @param  $applicationId
     * @return SettingsRepository
     */
    protected function getSettingRepository($applicationId)
    {
        return new SettingsRepository($this->getDatabaseHelper(), $applicationId);
    }

    /**
     * get router
     *
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->container->get("router");
    }

    /**
     * get logger
     *
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->container->get("logger");
    }

    /**
     * get logger
     *
     * @return RequestServiceInterface
     */
    protected function getRequestService()
    {
        return $this->container->get("requestService");
    }

    /**
     * redirects to the route specified in $slug
     *
     * @param  Request $request
     * @param  Response $response
     * @param  $slug
     * @return static
     */
    protected function redirect(Request $request, Response $response, $slug)
    {
        $reqUri = $request->getUri()->withPath($this->getRouter()->pathFor($slug));
        return $response->withRedirect($reqUri);
    }

    /**
     * check if $request contrails all specified properties
     *
     * @param  BaseRequest $request
     * @param  $neededProps
     * @param  null $neededArrays
     * @return bool
     */
    protected function isWellDefined(BaseRequest $request, $neededProps, $neededArrays = null)
    {
        if ($neededProps != null) {
            foreach ($neededProps as $neededProp) {
                /** @noinspection PhpVariableVariableInspection */
                if ($request->$neededProp == null) {
                    $this->getLogger()->log(
                        "not a property: " . $neededProp . 
                        " in object " . json_encode($request, JSON_PRETTY_PRINT), 
                        "isWellDefined_" . uniqid() . ".txt"
                    );
                    return false;
                }
            }
        }
        if ($neededArrays != null) {
            foreach ($neededArrays as $neededArray) {
                /** @noinspection PhpVariableVariableInspection */
                if (!is_array($request->$neededArray)) {
                    $this->getLogger()->log("not an array: " . $neededArray . 
                        " in object " . json_encode($request, JSON_PRETTY_PRINT), 
                        "isWellDefined_" . uniqid() . ".txt"
                    );
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * writes all properties from array to object, and returns all missing ones
     *
     * @param  array $source
     * @param  object $targetObject
     * @param  array $properties
     * @return array
     */
    protected function writePropertiesFromArray($source, $targetObject, $properties)
    {
        $missing = [];
        $keys = array_keys($source);
        foreach ($properties as $property) {
            if (in_array($property, $keys)) {
                /** @noinspection PhpVariableVariableInspection */
                $targetObject->$property = $source[$property];
            } else {
                $missing[] = $property;
            }
        }
        return $missing;
    }

    /**
     * returns model as json
     *
     * @param  Response $response
     * @param  $model
     * @return Response
     */
    protected function returnJsonObject(Response $response, $model)
    {
        $response->getBody()->write(json_encode($model));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
