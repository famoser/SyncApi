<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 14.11.2016
 * Time: 12:39
 */

namespace Famoser\SyncApi\Services;


use Famoser\SyncApi\Framework\Json\Models\ObjectProperty;
use Famoser\SyncApi\Framework\Json\SimpleJsonMapper;
use Famoser\SyncApi\Interfaces\IJsonDeserializable;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\HistoryEntityRequest;
use Famoser\SyncApi\Models\Communication\Request\SyncEntityRequest;
use Famoser\SyncApi\Services\Base\BaseService;
use Slim\Http\Request;

/**
 * the request service parses & validates requests
 *
 * @package Famoser\SyncApi\Services
 */
class RequestService extends BaseService
{
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
     * @return bool
     */
    public function validateAuthCode($authCode, $applicationSeed, $personSeed)
    {
        $content = explode("_", $authCode);
        //parse time from $content[0]
        $chunks = chunk_split($content[0], 2);
        if (count($chunks) != 4) {
            return false;
        }
        //check if time is valid
        $time = strtotime("today + " . $chunks[0] . " seconds " . $chunks[1] . " minutes " . $chunks[2] . " hours");
        $older = new \DateTime("+ 1 minute");
        $newer = new \DateTime("- 1 minute");
        if ($time < $newer && $time > $older) {
            //construct magic number (the same is done in c#)
            $baseNr = $chunks[0] + $chunks[1] * 100 + $chunks[2] * 10000 + $chunks[3];
            $expectedAuthCode = $baseNr * $applicationSeed * $personSeed;
            $expectedAuthCode %= $this->getModulo();
            return $content[1] == $expectedAuthCode;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param $model
     * @return object
     * @throws \JsonMapper_Exception
     */
    private function executeJsonMapper(Request $request, IJsonDeserializable $model)
    {
        if (isset($_POST["json"])) {
            $json = $_POST["json"];
        } else {
            $json = $request->getBody()->getContents();
        }

        $mapper = new SimpleJsonMapper();
        $om = new ObjectProperty("root", $model);
        $this->getLogger()->log(serialize($om->getProperties()["UserEntity"]), "log.txt");
        $resObj = $mapper->mapObject($json, $om);
        $this->getLogger()->log(json_encode($resObj, JSON_PRETTY_PRINT), "RequestHelper.txt");
        return $resObj;
    }
}