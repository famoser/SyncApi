<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 17:09
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'collections' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'device_guid'      TEXT    DEFAULT NULL,
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Types\ContentType;

/**
 * a collection is similar to a folder. a collection can be shared between multiple users
 * it is referenced by entities
 * @package Famoser\SyncApi\Models\Entities
 */
class Collection extends BaseSyncEntity
{
    /* @var string $user_guid type_of:guid */
    public $user_guid;

    /* @var string $device_guid type_of:guid */
    public $device_guid;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return 'collections';
    }

    /**
     * get the content type for the implementing model
     *
     * @return int
     */
    protected function getContentType()
    {
        return ContentType::COLLECTION;
    }

    /**
     * create the communication entity for the implementing model
     *
     * @return BaseCommunicationEntity
     */
    protected function createSpecificCommunicationEntity()
    {
        $entity = new CollectionCommunicationEntity();

        $entity->UserId = $this->user_guid;
        $entity->DeviceId = $this->device_guid;

        return $entity;
    }
}
