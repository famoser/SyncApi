<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 09:43
 */

namespace Famoser\SyncApi\Models\Response\Entities;


class AuthorizedDeviceEntity
{
    public $DeviceId;
    public $DeviceName;
    public $LastRequestDateTime;
    public $LastModificationDateTime;
    public $AuthorizationDateTime;
}