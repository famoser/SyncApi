<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 13/11/2016
 * Time: 12:27
 */

namespace Famoser\SyncApi\Exceptions;


use Famoser\SyncApi\Types\FrontendError;

class FrontendException extends \Exception
{
    public function __construct($frontendError)
    {
        parent::__construct(FrontendError::toString($frontendError), $frontendError, null);
    }
}