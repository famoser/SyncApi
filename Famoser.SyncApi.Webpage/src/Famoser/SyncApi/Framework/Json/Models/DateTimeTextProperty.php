<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 17.12.2016
 * Time: 15:59
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\AbstractJsonValueProperty;

/**
 * a date time json property, but produces 'c' text output
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class DateTimeTextProperty extends AbstractJsonValueProperty
{
    /**
     * converts an integer or string to time
     *
     * @param $value
     * @return string
     */
    public function parseValue($value)
    {
        return (new \DateTime($value))->format('c');
    }

    /**
     * returns a null object
     *
     * @return string
     */
    public function getNullValue()
    {
        return '';
    }
}