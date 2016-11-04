<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:22
 */

namespace Famoser\SyncApi\Models\Communication\Response;


use Famoser\SyncApi\Models\Communication\Entities\SyncEntity;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;

class SyncEntityResponse extends BaseResponse
{
    /* @var SyncEntity[] $SyncEntities */
    public $SyncEntities;
}