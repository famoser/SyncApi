<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:22
 */

namespace Famoser\SyncApi\Models\Communication\Response;


use Famoser\SyncApi\Models\Communication\Entities\SyncCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;

/**
 * the response to a SyncEntityRequest
 * @package Famoser\SyncApi\Models\Communication\Response
 */
class SyncEntityResponse extends BaseResponse
{
    /* @var SyncCommunicationEntity[] $SyncEntities */
    public $SyncEntities;
}
