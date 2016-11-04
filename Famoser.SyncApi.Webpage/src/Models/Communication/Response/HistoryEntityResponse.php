<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:21
 */

namespace Famoser\SyncApi\Models\Communication\Response;


use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;

class HistoryEntityResponse extends BaseResponse
{
    /* @var CollectionEntity[] $CollectionEntities */
    public $CollectionEntities;
}