<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 22:57
 */

namespace Famoser\SyncApi\Models\Request\Authorization;


use Famoser\SyncApi\Models\Request\Base\ApiRequest;

class UnAuthorizationRequest extends ApiRequest
{
    public $DeviceToBlockId;
    public $Reason;
}