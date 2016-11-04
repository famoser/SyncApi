<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:49
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'applications' (
  'id'                INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'admin_id'          INTEGER DEFAULT NULL REFERENCES 'frontend_users' ('id'),
  'name'              TEXT    DEFAULT NULL,
  'description'       TEXT    DEFAULT NULL,
  'application_id'    INT    DEFAULT NULL,
  'application_seed'  TEXT    DEFAULT NULL,
  'release_date_time' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Application extends BaseEntity
{
    /* @var int $id */
    public $admin_id;

    /* @var string $name */
    public $name;

    /* @var string $description */
    public $description;

    /* @var int $application_id */
    public $application_id;

    /* @var int $application_seed */
    public $application_seed;

    /* @var \DateTime $release_date */
    public $release_date_time;

    public function getTableName()
    {
        return "applications";
    }
}