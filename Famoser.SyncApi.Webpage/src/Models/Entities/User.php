<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:59
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'users' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'application_id'   INTEGER DEFAULT NULL REFERENCES 'applications' ('id'),
  'identifier'       TEXT    DEFAULT NULL,
  'guid'             TEXT    DEFAULT NULL,
  'personal_seed'    TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserCommunicationEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseSyncEntity;
use Famoser\SyncApi\Types\ContentType;

class User extends BaseSyncEntity
{
    /* @var string $application_id */
    public $application_id;

    /* @var string $personal_seed */
    public $personal_seed;

    /**
     * get the name of the table from the database
     *
     * @return string
     */
    public function getTableName()
    {
        return "users";
    }

    /**
     * get the content type for the implementing model
     *
     * @return int
     */
    protected function getContentType()
    {
        return ContentType::USER;
    }

    /**
     * create the communication entity for the implementing model
     *
     * @return BaseCommunicationEntity
     */
    protected function createSpecificCommunicationEntity()
    {
        return new UserCommunicationEntity();
    }
}
