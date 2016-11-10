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

use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Collection extends BaseEntity
{
    /* @var string $user_guid type_of:guid */
    public $user_guid;

    /* @var string $device_guid type_of:guid */
    public $device_guid;

    /* @var string $identifier */
    public $identifier;

    /* @var string $guid type_of:guid */
    public $guid;

    /* @var bool $is_deleted */
    public $is_deleted = false;

    public function writeFromEntity(CollectionEntity $entity)
    {
        $this->identifier = $entity->Identifier;
        $this->guid = $entity->Id;
    }

    public function getTableName()
    {
        return "collections";
    }
}
