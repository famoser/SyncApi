<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04/12/2016
 * Time: 12:55
 */

namespace Famoser\SyncApi\Tests;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;
use Famoser\SyncApi\Models\Communication\Request\CollectionEntityRequest;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Tests\ControllerTests\Base\ApiTestController;
use Famoser\SyncApi\Types\ContentType;
use Psr\Http\Message\ResponseInterface;

/**
 * helps asserting properties of response
 *
 * @package Famoser\SyncApi\Tests
 */
class AssertHelper
{
    /**
     * extract the response string from a response object
     *
     * @param ResponseInterface $response
     * @return string
     */
    public static function getResponseString(ResponseInterface $response)
    {
        $response->getBody()->rewind();
        return $response->getBody()->getContents();
    }

    /**
     * check if request was successful
     * returns the tested response string
     *
     * @param \PHPUnit_Framework_TestCase $testingUnit
     * @param ResponseInterface $response
     * @return string
     */
    public static function checkForSuccessfulApiResponse(
        \PHPUnit_Framework_TestCase $testingUnit,
        ResponseInterface $response
    )
    {
        //valid status code
        $testingUnit->assertEquals(200, $response->getStatusCode());

        //no error in json response
        $responseString = static::getResponseString($response);
        $testingUnit->assertContains("\"ApiError\":0", $responseString);
        $testingUnit->assertContains("\"RequestFailed\":false", $responseString);

        return $responseString;
    }

    /**
     * check if request was successful
     * returns the tested response string
     *
     * @param ApiTestController|\PHPUnit_Framework_TestCase $testingUnit
     * @param ResponseInterface $response
     * @param int $expectedApiError
     * @param int $expectedCode
     * @return string
     */
    public static function checkForFailedApiResponse(
        ApiTestController $testingUnit,
        ResponseInterface $response,
        $expectedApiError, $expectedCode = 500
    )
    {
        $responseString = static::getResponseString($response);

        //valid status code
        $testingUnit->assertEquals($expectedCode, $response->getStatusCode());

        //no error in json response
        $testingUnit->assertNotContains("\"ApiError\":0", $responseString);
        $testingUnit->assertContains("\"ApiError\":" . $expectedApiError, $responseString);
        $testingUnit->assertContains("\"RequestFailed\":true", $responseString);

        return $responseString;
    }

    /**
     * check if a saved entity exists & check if properties match
     *
     * @param ApiTestController $testController
     * @param BaseCommunicationEntity $collEntity
     * @param BaseSyncEntity $syncEntity
     * @param SyncApiApp $testApp
     * @param $entity
     * @param $entityVersion
     * @internal param ApiTestController $this
     * @internal param SyncApiApp $getTestApp
     */
    private static function checkForSavedSyncEntity(
        ApiTestController $testController,
        BaseCommunicationEntity $collEntity,
        BaseSyncEntity $syncEntity,
        SyncApiApp $testApp,
        &$entity,
        &$entityVersion)
    {
        $containerBase = new ContainerBase($testApp->getContainer());
        $databaseService = $containerBase->getDatabaseService();
        /* @var BaseSyncEntity $entity */
        $entity = $databaseService->getSingleFromDatabase(
            $syncEntity,
            "guid = :guid",
            ["guid" => $collEntity->Id]
        );
        $testController::assertNotNull($entity);
        $testController::assertEquals($collEntity->Identifier, $entity->identifier);
        /* @var ContentVersion $entityVersion */
        $entityVersion = $databaseService->getSingleFromDatabase(
            new ContentVersion(),
            "entity_guid = :guid",
            ["guid" => $collEntity->Id],
            "create_date_time DESC, id DESC"
        );
        $testController::assertNotNull($entityVersion);
        $testController::assertEquals($collEntity->Content, $entityVersion->content);
        $testController::assertEquals(
            (new \DateTime($collEntity->CreateDateTime))->getTimestamp(),
            $entityVersion->create_date_time
        );
        $testController::assertEquals($collEntity->Id, $entityVersion->entity_guid);
        $testController::assertEquals($collEntity->VersionId, $entityVersion->version_guid);

    }

    /**
     * check if collection is saved correctly to the database
     *
     * @param ApiTestController $testController
     * @param CollectionCommunicationEntity $collectionCommunicationEntity
     * @param SyncApiApp $syncApiApp
     * @param bool $deleted
     */
    public static function checkForSavedCollection(
        ApiTestController $testController,
        CollectionCommunicationEntity $collectionCommunicationEntity,
        SyncApiApp $syncApiApp,
        $deleted = false
    )
    {
        /* @var BaseSyncEntity $entity */
        $entity = null;
        /* @var ContentVersion $entityVersion */
        $entityVersion = null;
        static::checkForSavedSyncEntity(
            $testController,
            $collectionCommunicationEntity,
            new Collection(),
            $syncApiApp,
            $entity,
            $entityVersion
        );

        $testController::assertEquals($deleted, $entity->is_deleted);
        $testController::assertEquals($collectionCommunicationEntity->DeviceId, $entityVersion->device_guid);
        $testController::assertEquals(ContentType::COLLECTION, $entityVersion->content_type);

    }

    /**
     * check the response for the expected properties
     *
     * @param ApiTestController $testController
     * @param BaseCommunicationEntity $collEntity
     * @param BaseRequest $syncRequest
     * @param $communicationEntity
     */
    private static function checkResponseEntity(
        ApiTestController $testController,
        BaseCommunicationEntity $collEntity,
        BaseRequest $syncRequest,
        $communicationEntity
    )
    {
        /* @var BaseCommunicationEntity $communicationEntity */
        $testController::assertEquals($collEntity->VersionId, $communicationEntity->VersionId);
        $testController::assertEquals($collEntity->Content, $communicationEntity->Content);
        $testController::assertEquals(
            (new \DateTime($collEntity->CreateDateTime))->getTimestamp(),
            (new \DateTime($communicationEntity->CreateDateTime))->getTimestamp()
        );
        $testController::assertEquals($syncRequest->DeviceId, $communicationEntity->DeviceId);
        $testController::assertEquals($collEntity->Id, $communicationEntity->Id);
        $testController::assertEquals($collEntity->Identifier, $communicationEntity->Identifier);
    }

    /**
     * checks the response collection for the expected properties
     *
     * @param ApiTestController $testController
     * @param CollectionCommunicationEntity $collEntity
     * @param CollectionEntityRequest $syncRequest
     * @param $receivedCollection
     */
    public static function checkResponseCollection(
        ApiTestController $testController,
        CollectionCommunicationEntity $collEntity,
        CollectionEntityRequest $syncRequest,
        $receivedCollection
    )
    {
        static::checkResponseEntity($testController, $collEntity, $syncRequest, $receivedCollection);
        $testController::assertEquals($syncRequest->UserId, $receivedCollection->UserId);
    }
}