<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23/05/2016
 * Time: 21:13
 */

namespace Famoser\SyncApi\Models\Entities;


use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class User extends BaseEntity
{
    public $user_id;
    public $user_name;

    public function getTableName()
    {
        return "users";
    }
}