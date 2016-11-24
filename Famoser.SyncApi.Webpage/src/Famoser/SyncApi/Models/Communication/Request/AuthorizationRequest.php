<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:12
 */

namespace Famoser\SyncApi\Models\Communication\Request;


use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\DeviceCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

/**
 * an authorization request; is handled by the authorization controller and sent to /auth/*
 * @package Famoser\SyncApi\Models\Communication\Request
 */
class AuthorizationRequest extends BaseRequest
{
    /* @var UserCommunicationEntity $UserEntity */
    public $UserEntity;
    
    /* @var DeviceCommunicationEntity $DeviceEntity */
    public $DeviceEntity;
    
    /* @var CollectionCommunicationEntity $CollectionEntity */
    public $CollectionEntity;
    
    /* @var string $ClientMessage */
    public $ClientMessage;
}
