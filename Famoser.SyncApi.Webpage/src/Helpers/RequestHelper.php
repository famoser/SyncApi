<?php

namespace Famoser\SyncApi\Helpers;

use Famoser\SyncApi\Models\Request\Authorization\AuthorizationRequest;
use Famoser\SyncApi\Models\Request\Authorization\AuthorizationStatusRequest;
use Famoser\SyncApi\Models\Request\Authorization\AuthorizedDevicesRequest;
use Famoser\SyncApi\Models\Request\Authorization\CreateAuthorizationRequest;
use Famoser\SyncApi\Models\Request\Authorization\UnAuthorizationRequest;
use Famoser\SyncApi\Models\Request\CollectionEntriesRequest;
use Famoser\SyncApi\Models\Request\ContentEntityHistoryRequest;
use Famoser\SyncApi\Models\Request\ContentEntityRequest;
use Famoser\SyncApi\Models\Request\SyncRequest;
use Famoser\SyncApi\Models\Request\UpdateRequest;
use Famoser\SyncApi\Models\Response\Authorization\AuthorizedDevicesResponse;
use JsonMapper;
use Models\Request\Authorization\CreateUserRequest;
use Models\Request\Authorization\WipeUserRequest;
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
    public static function parseAuthorisationRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new AuthorizationRequest());
    }

    /**
     * @param Request $request
     * @return CreateUserRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseCreateUserRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new CreateUserRequest());
    }

    /**
     * @param Request $request
     * @return UnAuthorizationRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseUnAuthorisationRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new UnAuthorizationRequest());
    }

    /**
     * @param Request $request
     * @return AuthorizedDevicesRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseAuthorizedDevicesRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new AuthorizedDevicesRequest());
    }

    /**
     * @param Request $request
     * @return AuthorizationStatusRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseAuthorizationStatusRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new AuthorizationStatusRequest());
    }

    /**
     * @param Request $request
     * @return WipeUserRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseWipeUserRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new WipeUserRequest());
    }

    /**
     * @param Request $request
     * @return CreateAuthorizationRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseCreateAuthorizationRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new CreateAuthorizationRequest());
    }

    /**
     * @param Request $request
     * @return ContentEntityHistoryRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseContentEntityHistoryRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new ContentEntityHistoryRequest());
    }

    /**
     * @param Request $request
     * @return ContentEntityRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseContentEntityRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new ContentEntityRequest());
    }

    /**
     * @param Request $request
     * @return SyncRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseSyncRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new SyncRequest());
    }

    /**
     * @param Request $request
     * @return UpdateRequest
     * @throws \JsonMapper_Exception
     */
    public static function parseUpdateRequest(Request $request)
    {
        return RequestHelper::executeJsonMapper($request, new UpdateRequest());
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