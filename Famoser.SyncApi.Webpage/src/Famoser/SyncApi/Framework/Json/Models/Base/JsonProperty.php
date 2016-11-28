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
abstract class JsonProperty
{
    /* @var string $propertyName */
    private $propertyName;

    /**
     * JsonProperty constructor.
     *
     * @param $propertyName
     */
    public function __construct($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
}