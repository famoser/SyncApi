<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:12
 */

namespace Famoser\SyncApi\Exceptions;


use Famoser\SyncApi\Types\ServerError;

class ServerException extends \Exception
{
    public function __construct($serverError)
    {
        parent::__construct(ServerError::toString($serverError), $serverError, null);
    }
}