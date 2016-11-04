<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 17:07
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'user_collections' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'collection_guid'  TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class UserCollection extends BaseEntity
{
    public $user_guid;
    public $collection_guid;
    public $create_date_time;
    
    public function getTableName()
    {
        return "user_collections";
    }
}