<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04/12/2016
 * Time: 11:29
 */

namespace Famoser\SyncApi\Tests;


use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Types\OnlineAction;

/**
 * Class SampleGenerator: helps to generate sample data
 *
 * @package Famoser\SyncApi\Tests
 */
class SampleGenerator
{
    /**
     * create a guid
     *
     * @return string
     */
    public static function createGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     * create an entity
     *
     * @param BaseCommunicationEntity $entity
     */
    public static function createEntity(BaseCommunicationEntity $entity)
    {
        $entity->Id = static::createGuid();
        $entity->VersionId = static::createGuid();
        $entity->OnlineAction = OnlineAction::CREATE;
        $entity->Content = "{}";
        $entity->CreateDateTime = date("c");
        $entity->Identifier = "empty_json_obj";
    }
}