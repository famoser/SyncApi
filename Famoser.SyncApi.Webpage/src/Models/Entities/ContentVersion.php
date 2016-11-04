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
  'version_guid'     TEXT    DEFAULT NULL,
  'content'          TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class ContentVersion extends BaseEntity
{
    public $content_type;
    public $entity_guid;
    public $version_guid;
    public $content;
    public $create_date_time;

    public function getTableName()
    {
        return "content_versions";
    }
}