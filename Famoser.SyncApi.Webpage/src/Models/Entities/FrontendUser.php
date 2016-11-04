<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:51
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'frontend_users' (
  'id'       INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'email'    TEXT    DEFAULT NULL,
  'username' TEXT    DEFAULT NULL,
  'password' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class FrontendUser extends BaseEntity
{
    public $email;
    public $username;
    public $password;

    public function getTableName()
    {
        return "frontend_users";
    }
}