<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2016
 * Time: 17:44
 */

namespace Famoser\SyncApi\Models\Response\Authorization;


use Famoser\SyncApi\Models\Response\Base\ApiResponse;

class UnAuthorizationResponse extends ApiResponse
{
    public $Message;
}