<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 14:02
 */

namespace Famoser\SyncApi\Interfaces;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;

interface IJsonDeserializable
{
    /**
     * gets the json properties need to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties();
}