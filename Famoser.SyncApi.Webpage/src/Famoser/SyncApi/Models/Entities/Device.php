<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 17:10
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'devices' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL,
  'is_authenticated' BOOLEAN DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Types\ContentType;

/**
 * a device identifies the device of an user
 * @package Famoser\SyncApi\Models\Entities
 */
class Device extends BaseSyncEntity
{
    /* @var string $user_guid type_of:guid */
    public $user_guid;

    /* @var bool $is_authenticated */
    public $is_authenticated = false;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return "devices";
    }

    /**
     * get the content type for the implementing model
     *
     * @return int
     */
    protected function getContentType()
    {
        return ContentType::DEVICE;
    }

    /**
     * create the communication entity for the implementing model
     *
     * @return BaseCommunicationEntity
     */
    protected function createSpecificCommunicationEntity()
    {
        $entity = new DeviceCommunicationEntity();

        $entity->UserId = $this->user_guid;

        return $entity;
    }
}
