<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 14:33
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;

/**
 * an array property. contains information about the objects or values contained in the array
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class ArrayProperty extends JsonValueProperty
{
    /* @var JsonProperty $properties */
    private $objectProperty;

    /**
     * ArrayProperty constructor.
     *
     * @param $propertyName
     * @param $objectProperty
     */
    public function __construct($propertyName, $objectProperty)
    {
        parent::__construct($propertyName);
        $this->objectProperty = $objectProperty;
    }

    /**
     * @return JsonProperty
     */
    public function getProperty()
    {
        return $this->objectProperty;
    }

    /**
     * parse the value
     *
     * @param $value
     * @return mixed
     */
    public function parseValue($value)
    {
        //can't just parse this! The JsonMapper must take care of this
        return [];
    }

    /**
     * return the default value for the value
     *
     * @return mixed
     */
    public function getNullValue()
    {
        return [];
    }
}