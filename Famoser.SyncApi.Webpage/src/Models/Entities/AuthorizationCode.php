<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23/05/2016
 * Time: 21:16
 */

namespace Famoser\SyncApi\Models\Entities;


use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class AuthorizationCode extends BaseEntity
{
    public $user_id;
    public $code;
    public $content;
    public $valid_till;

    public function getTableName()
    {
        return "authorization_codes";
    }
}