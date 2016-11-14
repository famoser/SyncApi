<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14.11.2016
 * Time: 12:39
 */

namespace Famoser\SyncApi\Services;


use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\HistoryEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\Services\Interfaces\LoggerInterface;
use JsonMapper;
use Slim\Http\Request;

class RequestService
{
    /* @var LoggerInterface $logger */
    private $logger;

    /**
     * RequestService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return AuthorizationRequest
     * @throws \JsonMapper_Exception
     */
    public function parseAuthorizationRequest(Request $request)
    {
        return $this->executeJsonMapper($request, new AuthorizationRequest());
    }

    /**
     * @param Request $request
     * @return CollectionEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseCollectionEntityRequest(Request $request)
    {
        return $this->executeJsonMapper($request, new CollectionEntityRequest());
    }

    /**
     * @param Request $request
     * @return HistoryEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseHistoryEntityRequest(Request $request)
    {
        return $this->executeJsonMapper($request, new HistoryEntityRequest());
    }

    /**
     * @param Request $request
     * @return SyncEntityRequest
     * @throws \JsonMapper_Exception
     */
    public function parseSyncEntityRequest(Request $request)
    {
        return $this->executeJsonMapper($request, new SyncEntityRequest());
    }

    /**
     * @param $authCode
     * @param $applicationSeed
     * @param $personSeed
     * @param int $modulo
     * @return bool
     */
    public function validateAuthCode($authCode, $applicationSeed, $personSeed, $modulo = 10000019)
    {
        $content = explode("_", $authCode);
        $expectedAuthCode = $content[0] * $applicationSeed * $personSeed;
        $expectedAuthCode %= $modulo;
        return $authCode == $expectedAuthCode;
    }

    /**
     * @param Request $request
     * @param $model
     * @return object
     * @throws \JsonMapper_Exception
     */
    private function executeJsonMapper(Request $request, $model)
    {
        if (isset($_POST["json"])) {
            $jsonObj = json_decode($_POST["json"]);
        } else {
            $jsonObj = json_decode($request->getBody()->getContents());
        }

        $mapper = new JsonMapper();
        $mapper->bExceptionOnUndefinedProperty = true;
        $resObj = $mapper->map($jsonObj, $model);
        $this->logger->log(json_encode($resObj, JSON_PRETTY_PRINT), "RequestHelper.txt");
        return $resObj;
    }
}