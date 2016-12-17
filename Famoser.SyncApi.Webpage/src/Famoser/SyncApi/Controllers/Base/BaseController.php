<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27.05.2016
 * Time: 14:23
 */

namespace Famoser\SyncApi\Controllers\Base;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Repositories\SettingsRepository;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * the base controller which provides access to the environment
 *
 * Class BaseController
 * @package Famoser\SyncApi\Controllers\Base
 */
class BaseController extends ContainerBase
{
    /**
     * get SettingsRepository for the specified application
     *
     * @param  int $applicationId
     * @return SettingsRepository
     */
    protected function getSettingRepository($applicationId)
    {
        return new SettingsRepository($this->getDatabaseService(), $applicationId);
    }

    /**
     * redirects to the route specified in $slug
     *
     * @param  Request $request
     * @param  Response $response
     * @param  string $slug
     * @return Response
     */
    protected function redirect(Request $request, Response $response, $slug)
    {
        $reqUri = $request->getUri()->withPath($this->getRouter()->pathFor($slug));
        return $response->withRedirect($reqUri);
    }

    /**
     * writes all properties from array to object, and returns all missing ones
     *
     * @param  array $source
     * @param  \Famoser\SyncApi\Models\Entities\Application $targetObject
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
     * @param  \Famoser\SyncApi\Models\ApiInformation $model
     * @return Response
     */
    protected function returnJsonObject(Response $response, $model)
    {
        $response->getBody()->write(json_encode($model));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
