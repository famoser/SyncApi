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
  'password' TEXT    DEFAULT NULL,
  'reset_key' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

/**
 * a frontend user is used in the web application, it can manage and create applications
 * @package Famoser\SyncApi\Models\Entities
 */
class FrontendUser extends BaseEntity
{
    /* @var string $email type_of:email */
    public $email;

    /* @var string $username */
    public $username;

    /* @var string $password type_of:password_hash */
    public $password;

    /* @var string $reset_key type_of:random_hash */
    public $reset_key;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return 'frontend_users';
    }
}
