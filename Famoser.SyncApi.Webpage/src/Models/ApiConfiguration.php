<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/06/2016
 * Time: 18:19
 */

namespace Famoser\SyncApi\Models;


class ApiConfiguration
{
    public $Uri;
    public $GenerationSalt;
    public $GenerationKeyLenghtInBytes;
    public $GenerationKeyInterations;
    public $InitialisationVector;
}