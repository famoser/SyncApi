<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:55
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'entities' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'device_guid'      TEXT    DEFAULT NULL,
  'collection_guid'  TEXT    DEFAULT NULL,
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Entity extends BaseEntity
{
    public $user_guid;
    public $device_guid;
    public $collection_guid;
    public $identifier;
    public $guid;
    
    public function getTableName()
    {
        return "entities";
    }
}