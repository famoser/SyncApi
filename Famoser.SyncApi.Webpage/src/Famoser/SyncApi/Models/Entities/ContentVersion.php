<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:57
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'content_versions' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'content_type'     INTEGER DEFAULT NULL,
  'entity_guid'      TEXT    DEFAULT NULL,
  'device_guid'      TEXT    DEFAULT NULL,
  'version_guid'     TEXT    DEFAULT NULL,
  'content'          TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

/**
 * represents the content of an entity at a specific point in time
 * @package Famoser\SyncApi\Models\Entities
 */
class ContentVersion extends BaseEntity
{
    /* @var int $content_type const_of:ContentType */
    public $content_type;

    /* @var string $entity_guid type_of:guid */
    public $entity_guid;

    /* @var string $device_guid type_of:guid */
    public $device_guid;

    /* @var string $version_guid type_of:guid */
    public $version_guid;

    /* @var string $content */
    public $content;

    /* @var int $create_date_time type_of:DateTime */
    public $create_date_time;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return "content_versions";
    }
}
