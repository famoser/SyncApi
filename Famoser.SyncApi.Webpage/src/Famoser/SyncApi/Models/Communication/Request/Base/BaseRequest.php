<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:10
 */

namespace Famoser\SyncApi\Models\Communication\Request\Base;


/**
 * a base request
 * contains properties which every request may fill out with very few exceptions
 * @package Famoser\SyncApi\Models\Communication\Request\Base
 */
class BaseRequest
{
    /* @var string $UserId type_of:guid*/
    public $UserId;

    /* @var string $DeviceId type_of:guid */
    public $DeviceId;

    /* @var string $AuthorizationCode */
    public $AuthorizationCode;

    /* @var string $ApplicationId */
    public $ApplicationId;
}
