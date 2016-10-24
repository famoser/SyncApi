<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 09:40
 */

namespace Famoser\SyncApi\Models\Response\Authorization;


use Famoser\SyncApi\Models\Response\Base\ApiResponse;

class AuthorizationResponse extends ApiResponse
{
    public $Message;
    public $Content;
}