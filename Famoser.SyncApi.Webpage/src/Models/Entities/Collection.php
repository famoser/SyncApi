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

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Collection extends BaseEntity
{
    public $user_guid;
    public $device_guid;
    public $identifier;
    public $guid;

    public function getTableName()
    {
        return "collections";
    }
}