<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 05.11.2016
 * Time: 18:01
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'application_settings' (
  'id'              INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'application_id'  INTEGER DEFAULT NULL REFERENCES 'applications' ('id'),
  'key'             TEXT    DEFAULT NULL,
  'val'             TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class ApplicationSetting extends BaseEntity
{
    /* @var int $application_id */
    public $application_id;

    /* @var string $key */
    public $key;

    /* @var string $val */
    public $val;

    public function getTableName()
    {
        return "application_settings";
    }
}