<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:20
 */

namespace Famoser\SyncApi\Models\Communication\Response;


use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;

class AuthorizationResponse extends BaseResponse
{
    /* @var UserCommunicationEntity $UserEntity */
    public $UserEntity;

    /* @var DeviceCommunicationEntity $DeviceEntity */
    public $DeviceEntity;
}
