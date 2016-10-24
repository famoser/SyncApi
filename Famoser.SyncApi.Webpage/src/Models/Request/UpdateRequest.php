<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2016
 * Time: 17:41
 */

namespace Famoser\SyncApi\Models\Request;


use Famoser\SyncApi\Models\Request\Base\ApiRequest;

class UpdateRequest extends ApiRequest
{
    public $ContentId;
    public $CollectionId;
    public $VersionId;
}