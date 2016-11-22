<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07.11.2016
 * Time: 11:12
 */

namespace Famoser\SyncApi\Exceptions;


use Famoser\SyncApi\Types\ServerError;

/**
 * a server exception occurs, if a critical action on the server fails (like saving to the database)
 * but the user has no way of changing anything of that behaviour. The user should not receive an unhelpful
 * error message, but rather a "sorry, but we can't continue" kind of behaviour
 * @package Famoser\SyncApi\Exceptions
 */
class ServerException extends \Exception
{
    /**
     * ServerException constructor.
     * @param string $serverError
     */
    public function __construct($serverError)
    {
        parent::__construct(ServerError::toString($serverError), $serverError, null);
    }
}
