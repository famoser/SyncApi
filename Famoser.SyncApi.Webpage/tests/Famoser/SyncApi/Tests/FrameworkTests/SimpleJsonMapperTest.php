<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 17:43
 */

namespace Famoser\SyncApi\Tests\FrameworkTests;

use Famoser\SyncApi\Framework\Json\Models\ObjectProperty;
use Famoser\SyncApi\Framework\Json\SimpleJsonMapper;
use Famoser\SyncApi\Models\Communication\Request\AuthorizationRequest;


/**
 * some php functionality tests
 * @package Famoser\SyncApi\Tests
 */
class SimpleJsonMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tests the SimpleJsonMapper
     */
    public function testTesting()
    {
        $jsonMapper = new SimpleJsonMapper();
        /* @var AuthorizationRequest $res */
        $res = $jsonMapper->mapObject('
            {
                "UserEntity": {
                    "PersonalSeed": 621842297,
                    "Id": "da66416e-767d-4687-a2af-353b47a0e5c1",
                    "VersionId": "6b73667e-0229-4350-9c0e-831845bbda8f",
                    "OnlineAction": 1,
                    "Content": "{}",
                    "CreateDateTime": "2016-11-28T12:43:13+01:00",
                    "Identifier": "user"
                },
                "DeviceEntity": null,
                "CollectionEntity": null,
                "ClientMessage": null,
                "UserId": "da66416e-767d-4687-a2af-353b47a0e5c1",
                "DeviceId": "00000000-0000-0000-0000-000000000000",
                "AuthorizationCode": "13431239_-8215860",
                "ApplicationId": "test_appl"
            }',
            new ObjectProperty("root", new AuthorizationRequest())
        );
        static::assertNotNull($res);
        static::assertNotNull($res->UserEntity);
        static::assertNull($res->ClientMessage);
        static::assertNull($res->CollectionEntity);
        static::assertEquals($res->UserId, "da66416e-767d-4687-a2af-353b47a0e5c1");
        static::assertEquals($res->DeviceId, "00000000-0000-0000-0000-000000000000");
        static::assertEquals($res->AuthorizationCode, "13431239_-8215860");
        static::assertEquals($res->ApplicationId, "test_appl");
        static::assertEquals((new \DateTime($res->UserEntity->CreateDateTime))->format("c"), "2016-11-28T12:43:13+01:00");
        static::assertEquals($res->UserEntity->VersionId, "6b73667e-0229-4350-9c0e-831845bbda8f");
    }
}
