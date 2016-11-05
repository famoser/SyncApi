<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:59
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'users' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'application_id'   INTEGER DEFAULT NULL REFERENCES 'applications' ('id'),
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL,
  'personal_seed'    TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class User extends BaseEntity
{
    /* @var string $application_id */
    public $application_id;

    /* @var string $identifier */
    public $identifier;

    /* @var string $guid type_of:guid */
    public $guid;

    /* @var string $personal_seed */
    public $personal_seed;
    
    public function getTableName()
    {
        return "users";
    }
}