<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23/05/2016
 * Time: 21:17
 */

namespace Famoser\SyncApi\Models\Entities;


use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Content extends BaseEntity
{
    public $content_id;
    public $user_id;
    public $collection_id;
    public $version_id;
    public $device_id;
    public $creation_date_time;

    public function getTableName()
    {
        return "content";
    }
}