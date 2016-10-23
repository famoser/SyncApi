<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 09:42
 */

namespace Famoser\SyncApi\Models\Response\Authorization;


use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Models\Response\Entities\AuthorizedDeviceEntity;

class AuthorizedDevicesResponse extends ApiResponse
{
    /**
     * @var \Famoser\SyncApi\Models\Response\Entities\AuthorizedDeviceEntity
     */
    public $AuthorizedDeviceEntities;
}