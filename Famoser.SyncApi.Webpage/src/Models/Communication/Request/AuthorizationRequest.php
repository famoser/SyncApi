<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:12
 */

namespace Famoser\SyncApi\Models\Communication\Request;


use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Communication\Entities\DeviceEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

class AuthorizationRequest extends BaseRequest
{
    /* @var UserEntity $UserEntity */
    public $UserEntity;
    
    /* @var DeviceEntity $DeviceEntity */
    public $DeviceEntity;
    
    /* @var CollectionEntity $CollectionEntity */
    public $CollectionEntity;
    
    /* @var string $ClientMessage */
    public $ClientMessage;
}
