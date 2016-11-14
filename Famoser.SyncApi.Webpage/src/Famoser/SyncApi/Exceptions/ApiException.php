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

class ApiException extends Exception
{
    public function __construct($apiCode)
    {
        parent::__construct(ApiError::toString($apiCode), $apiCode, null);
    }
}
