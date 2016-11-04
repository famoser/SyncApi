<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:06
 */

namespace Famoser\SyncApi\Models\Communication\Entities;


use Famoser\SyncApi\Models\Communication\Entities\Base\BaseEntity;

class DeviceEntity extends BaseEntity
{
    /* @var string $UserId type_of:guid */
    public $UserId;
}