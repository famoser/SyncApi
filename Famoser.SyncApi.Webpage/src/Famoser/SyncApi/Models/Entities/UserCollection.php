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

/**
 * a user collection is the connection between an user and its collections
 * @package Famoser\SyncApi\Models\Entities
 */
class UserCollection extends BaseEntity
{
    /* @var string $user_guid type_of:guid */
    public $user_guid;

    /* @var string $collection_guid type_of:guid */
    public $collection_guid;

    /* @var \DateTime $create_date_time */
    public $create_date_time;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return "user_collections";
    }
}
