<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 12:28
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;

class DateTimeProperty extends JsonValueProperty
{
    public function parseValue($value)
    {
        return new \DateTime(strtotime($value));
    }

    public function getNullValue()
    {
        return null;
    }
}