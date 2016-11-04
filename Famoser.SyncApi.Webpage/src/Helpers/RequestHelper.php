<?php

namespace Famoser\SyncApi\Helpers;

use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\HistoryEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use JsonMapper;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 23:35
 */
class RequestHelper
{
    /**
     * @param Request $request
     * @return AuthorizationRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseAuthorizationRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new AuthorizationRequest());
    }

    /**
     * @param Request $request
     * @return CollectionEntityRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseCollectionEntityRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new CollectionEntityRequest());
    }

    /**
     * @param Request $request
     * @return HistoryEntityRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseHistoryEntityRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new HistoryEntityRequest());
    }

    /**
     * @param Request $request
     * @return SyncEntityRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseSyncEntityRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new SyncEntityRequest());
    }

    private static function executeJsonMapper(Request $request, $model)
    {
        if (isset($_POST["json"]))
            $jsonObj = json_decode($_POST["json"]);
        else
            $jsonObj = json_decode($request->getBody()->getContents());

        $mapper = new JsonMapper();
        $mapper->bExceptionOnUndefinedProperty = true;
        $resObj = $mapper->map($jsonObj, $model);
        LogHelper::log(json_encode($resObj, JSON_PRETTY_PRINT), "RequestHelper.txt");
        return $resObj;
    }
}