<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 14:13
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;
use Famoser\SyncApi\Interfaces\IJsonDeserializable;

/**
 * object json property. hold json info about the object that should be created there
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class ObjectProperty extends JsonProperty
{
    /* @var string $className */
    private $className;
    /* @var JsonValueProperty[] $properties */
    private $properties;

    /**
     * ObjectProperty constructor.
     *
     * @param string $propertyName
     * @param IJsonDeserializable $class
     */
    public function __construct($propertyName, IJsonDeserializable $class)
    {
        parent::__construct($propertyName);
        $this->className = get_class($class);
        $this->properties = $class->getJsonProperties();
    }

    /**
     * returns properties of the object
     * 
     * @return JsonValueProperty[]
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
}