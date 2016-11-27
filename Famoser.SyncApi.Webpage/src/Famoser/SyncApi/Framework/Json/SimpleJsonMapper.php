<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 26/11/2016
 * Time: 23:43
 */

namespace Famoser\SyncApi\Framework\Json;


use Famoser\SyncApi\Framework\Json\Models\ArrayProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;
use Famoser\SyncApi\Framework\Json\Models\ObjectProperty;

class SimpleJsonMapper
{
    /**
     * maps the json to real objects with the configuration provided
     *
     * @param string $json
     * @param ObjectProperty $property
     * @return object
     */
    public function mapObject($json, ObjectProperty $property)
    {
        $content = json_decode($json, true);
        return $this->mapObjectInternal($content, $property);
    }

    /**
     * @param $json
     * @param ArrayProperty $property
     * @return array
     */
    public function mapArray($json, ArrayProperty $property)
    {
        $content = json_decode($json, true);
        return $this->mapArrayInternal($content, $property);
    }

    /**
     * @param $content
     * @param ObjectProperty $property
     * @return object
     */
    private function mapObjectInternal($content, ObjectProperty $property)
    {
        $inst = $property->getInstance();
        foreach ($property->getProperties() as $key => $property) {
            $jsonPropertyName = $property->getPropertyName();
            if ($property instanceof JsonValueProperty) {
                if (isset($content[$jsonPropertyName])) {
                    $inst->$key = $property->parseValue($content[$jsonPropertyName]);
                } else {
                    $inst->$key = $property->getNullValue();
                }
            } else if ($property instanceof ArrayProperty) {
                if (isset($content[$jsonPropertyName])) {
                    $inst->$key = $this->mapArrayInternal($content, $property);
                } else {
                    $inst->$key = [];
                }
            } else if ($property instanceof ObjectProperty) {
                if (isset($content[$jsonPropertyName])) {
                    $inst->$key = $this->mapObject($content[$jsonPropertyName], $property);
                } else {
                    $inst->$key = null;
                }
            }
        }
        return $inst;
    }

    /**
     * @param $content
     * @param ArrayProperty $property
     * @return array
     */
    private function mapArrayInternal($content, ArrayProperty $property)
    {
        $res = [];
        foreach ($content as $arrayContent) {
            $prop = $property->getProperty();
            if ($prop instanceof ObjectProperty) {
                $res[] = $this->mapObject($arrayContent, $prop);
            } else if ($prop instanceof JsonValueProperty) {
                $res[] = $prop->parseValue($arrayContent);
            }
        }
        return $res;
    }
}