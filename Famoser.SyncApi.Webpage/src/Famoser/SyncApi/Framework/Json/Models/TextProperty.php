<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 12:39
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;

class TextProperty extends JsonValueProperty
{
    public function parseValue($value)
    {
        return $value;
    }

    public function getNullValue()
    {
        return null;
    }
}