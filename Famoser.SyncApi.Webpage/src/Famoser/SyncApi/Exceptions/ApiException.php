<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 10:08
 */

namespace Famoser\SyncApi\Exceptions;


use Exception;
use Famoser\SyncApi\Types\ApiError;

/**
 * raised if an error in processing the request has occurred which the user has to know
 * @package Famoser\SyncApi\Exceptions
 */
class ApiException extends Exception
{
    /**
     * ApiException constructor.
     * @param string $apiCode
     */
    public function __construct($apiCode)
    {
        parent::__construct(ApiError::toString($apiCode), $apiCode, null);
    }
}
