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
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'admin_id'         INTEGER DEFAULT NULL REFERENCES 'frontend_users' ('id'),
  'name'             TEXT    DEFAULT NULL,
  'description'      TEXT    DEFAULT NULL,
  'application_id'   TEXT    DEFAULT NULL,
  'application_seed' TEXT    DEFAULT NULL,
  'release_date'     TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Application extends BaseEntity
{
    public $admin_id;
    public $name;
    public $description;
    public $application_id;
    public $application_seed;
    public $release_date;

    public function getTableName()
    {
        return "applications";
    }
}