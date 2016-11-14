<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:06
 */

namespace Famoser\SyncApi\Models\Communication\Entities;


use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;

class DeviceCommunicationEntity extends BaseCommunicationEntity
{
    /* @var string $UserId type_of:guid */
    public $UserId;
}
