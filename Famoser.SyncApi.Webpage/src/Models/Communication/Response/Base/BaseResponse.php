<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:18
 */

namespace Famoser\SyncApi\Models\Communication\Response\Base;


use Famoser\SyncApi\Types\ApiError;

class BaseResponse
{
    /* @var ApiError $ApiError */
    public $ApiError;

    /* @var string $ServerMessage */
    public $ServerMessage;

    /* @var bool $RequestFailed */
    public $RequestFailed;
}