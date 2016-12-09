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
        $testingUnit->assertTrue($response->getStatusCode() == 200);

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
        $testingUnit->assertTrue($response->getStatusCode() == $expectedCode);

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
        $testController::assertEquals($entity->identifier, $collEntity->Identifier);
        /* @var ContentVersion $entityVersion */
        $entityVersion = $databaseService->getSingleFromDatabase(
            new ContentVersion(),
            "entity_guid = :guid",
            ["guid" => $collEntity->Id]
        );
        $testController::assertNotNull($entityVersion);
        $testController::assertEquals($entityVersion->content, $collEntity->Content);
        $testController::assertEquals($entityVersion->create_date_time, (new \DateTime($collEntity->CreateDateTime))->getTimestamp());
        $testController::assertEquals($entityVersion->entity_guid, $collEntity->Id);
        $testController::assertEquals($entityVersion->version_guid, $collEntity->VersionId);

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

        $testController::assertEquals($entity->is_deleted, $deleted);
        $testController::assertEquals($entityVersion->device_guid, $collectionCommunicationEntity->DeviceId);
        $testController::assertEquals($entityVersion->content_type, ContentType::COLLECTION);

    }
}