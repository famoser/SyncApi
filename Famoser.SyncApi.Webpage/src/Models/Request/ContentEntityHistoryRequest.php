<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 23:01
 */

namespace Famoser\SyncApi\Models\Request;


use Famoser\SyncApi\Models\Request\Base\ApiRequest;

class ContentEntityHistoryRequest extends ApiRequest
{
    public $ContentId;
}