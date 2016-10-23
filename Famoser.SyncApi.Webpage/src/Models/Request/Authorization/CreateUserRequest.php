<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 18/09/2016
 * Time: 16:57
 */

namespace Models\Request\Authorization;


use Famoser\SyncApi\Models\Request\Base\ApiRequest;

class CreateUserRequest extends ApiRequest
{
    public $UserName;
    public $DeviceName;
}