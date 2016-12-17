<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 27/11/2016
 * Time: 12:39
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;

/**
 * Class TextProperty
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class BooleanProperty extends JsonValueProperty
{
    /**
     * returns a string
     *
     * @param $value
     * @return boolean
     */
    public function parseValue($value)
    {
        if ($value == 1 || $value == 'true') {
            return true;
        }
        return false;
    }

    /**
     * returns null
     *
     * @return boolean
     */
    public function getNullValue()
    {
        return false;
    }
}