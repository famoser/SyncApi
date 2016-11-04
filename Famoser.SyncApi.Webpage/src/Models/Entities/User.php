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
  'guid'             TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class User extends BaseEntity
{
    public $application_id;
    public $identifier;
    public $guid;
    
    public function getTableName()
    {
        return "users";
    }
}