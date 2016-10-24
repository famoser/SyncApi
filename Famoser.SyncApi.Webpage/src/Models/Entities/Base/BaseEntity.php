<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23/05/2016
 * Time: 21:15
 */

namespace Famoser\SyncApi\Models\Entities\Base;


abstract class BaseEntity
{
    public $id;
    abstract public function getTableName();
}