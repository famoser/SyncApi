<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14.11.2016
 * Time: 12:38
 */

namespace Famoser\SyncApi\Services\Interfaces;


use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\HistoryEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Slim\Http\Request;

/**
 * an interface to a request service; it parses & validates requests
 * @package Famoser\SyncApi\Services\Interfaces
 */
interface RequestServiceInterface
{
    /**
     * @param Request $request
     * @return AuthorizationRequest
     * @throws \JsonMapper_Exception
     */
    public function parseAuthorizationRequest(Request $request);

    /**
     * @param Request $request
     * @return CollectionEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseCollectionEntityRequest(Request $request);

    /**
     * @param Request $request
     * @return HistoryEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseHistoryEntityRequest(Request $request);

    /**
     * @param Request $request
     * @return SyncEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseSyncEntityRequest(Request $request);

    /**
     * @param string $authCode
     * @param integer $applicationSeed
     * @param string $personSeed
     * @param int $modulo
     * @return bool
     */
    public function isAuthenticationCodeValid($authCode, $applicationSeed, $personSeed, $modulo = 10000019);
}