<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 10:34
 */

namespace Famoser\SyncApi\Framework\Json\Models\Base;

/**
 * specifies information about how a property has to be deserialized
 *
 * @package Famoser\SyncApi\Framework\Json\Models\Base
 */
abstract class JsonValueProperty extends JsonProperty
{
    /**
     * parse the value
     *
     * @param $value
     * @return mixed
     */
    abstract public function parseValue($value);

    /**
     * return the default value for the value
     *
     * @return mixed
     */
    abstract public function getNullValue();
}