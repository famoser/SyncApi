<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:18
 */

namespace Famoser\SyncApi\Models\Communication\Response\Base;


use Famoser\SyncApi\Types\ApiError;

/**
 * some properties which every response contains
 * @package Famoser\SyncApi\Models\Communication\Response\Base
 */
class BaseResponse
{
    /* @var ApiError $ApiError: contains the Error which might have occurred, see the errors in the ApiError class */
    public $ApiError;

    /* @var string $ServerMessage: a message from the server, content type depends on request */
    public $ServerMessage;

    /* @var bool $RequestFailed: a boolean which indicates if the request was successful */
    public $RequestFailed = false;
}
