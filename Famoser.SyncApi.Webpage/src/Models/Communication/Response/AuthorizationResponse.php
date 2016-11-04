<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:20
 */

namespace Famoser\SyncApi\Models\Communication\Response;


use Famoser\SyncApi\Models\Communication\Entities\DeviceEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserEntity;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;

class AuthorizationResponse extends BaseResponse
{
    /* @var UserEntity $UserEntity */
    public $UserEntity;

    /* @var DeviceEntity $DeviceEntity */
    public $DeviceEntity;
}