<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28.11.2016
 * Time: 15:37
 */

namespace Famoser\SyncApi\Framework\Json\Models;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;

/**
 * integer json property
 *
 * @package Famoser\SyncApi\Framework\Json\Models
 */
class IntProperty extends JsonValueProperty
{
    /**
     * returns an integer
     *
     * @param $value
     * @return int
     */
    public function parseValue($value)
    {
        return (int)$value;
    }

    /**
     * returns 0
     *
     * @return int
     */
    public function getNullValue()
    {
        return 0;
    }
}