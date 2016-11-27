<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 10:34
 */

namespace Famoser\SyncApi\Framework\Json\Models\Base;

/*
 * specifies information about how a property has to be deserialized
 */
abstract class JsonValueProperty extends JsonProperty
{
    abstract public function parseValue($value);
    abstract public function getNullValue();
}