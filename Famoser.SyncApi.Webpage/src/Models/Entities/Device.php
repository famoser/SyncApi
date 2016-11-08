<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 17:10
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'devices' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'user_guid'        TEXT    DEFAULT NULL,
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL,
  'is_authenticated' BOOLEAN DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Communication\Entities\DeviceEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;

class Device extends BaseEntity
{
    /* @var string $user_guid type_of:guid */
    public $user_guid;
    
    /* @var string $identifier */
    public $identifier;

    /* @var string $guid type_of:guid */
    public $guid;

    /* @var bool $is_authenticated */
    public $is_authenticated;

    public function getTableName()
    {
        return "devices";
    }
}