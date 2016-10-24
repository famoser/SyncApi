<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 09:41
 */

namespace Famoser\SyncApi\Models\Response\Base;


use Famoser\SyncApi\Types\ApiError;

class ApiResponse
{
    public function __construct($successfull = true, $apiError = ApiError::None)
    {
        $this->Successfull = $successfull;
        $this->ApiError = $apiError;
    }

    public $Successfull;
    public $ApiError;
    public $ApiMessage;
}