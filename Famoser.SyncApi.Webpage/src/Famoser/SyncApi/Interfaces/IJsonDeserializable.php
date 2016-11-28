<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 14:02
 */

namespace Famoser\SyncApi\Interfaces;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;

/**
 * Interface IJsonDeserializable
 * if you plan to use this object with the SimpleJsonMapper, implement this interface
 *
 * @package Famoser\SyncApi\Interfaces
 */
interface IJsonDeserializable
{
    /**
     * gets the json properties need to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties();
}