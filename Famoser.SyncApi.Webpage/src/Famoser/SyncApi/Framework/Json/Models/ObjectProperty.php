<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 14:13
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\AbstractJsonProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\AbstractJsonValueProperty;
use Famoser\SyncApi\Interfaces\JsonDeserializableInterface;

/**
 * object json property. hold json info about the object that should be created there
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class ObjectProperty extends AbstractJsonValueProperty
{
    /* @var string $className */
    private $className;
    /* @var AbstractJsonValueProperty[] $properties */
    private $properties = [];

    /**
     * ObjectProperty constructor.
     *
     * @param string $propertyName
     * @param JsonDeserializableInterface $class
     */
    public function __construct($propertyName, JsonDeserializableInterface $class)
    {
        parent::__construct($propertyName);
        $this->className = get_class($class);
        $props = $class->getJsonProperties();
        foreach ($props as $key => $prop) {
            if ($prop instanceof AbstractJsonValueProperty) {
                $this->properties[$key] = $prop;
            } else {
                break;
            }
        }
    }

    /**
     * returns properties of the object
     *
     * @return AbstractJsonValueProperty[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * constructs an instance of the object
     *
     * @return object
     */
    public function getInstance()
    {
        return new $this->className();
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
        return null;
    }

    /**
     * return the default value for the value
     *
     * @return mixed
     */
    public function getNullValue()
    {
        return null;
    }
}