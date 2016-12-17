<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 12:28
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\AbstractJsonValueProperty;

/**
 * a date time json property
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class DateTimeProperty extends AbstractJsonValueProperty
{
    /**
     * converts an integer or string to time
     *
     * @param $value
     * @return \DateTime
     */
    public function parseValue($value)
    {
        return new \DateTime($value);
    }

    /**
     * returns a null object
     *
     * @return null
     */
    public function getNullValue()
    {
        return null;
    }
}