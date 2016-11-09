<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 16:57
 */

namespace Famoser\SyncApi\Models\Entities;

/*
CREATE TABLE 'content_versions' (
  'id'               INTEGER DEFAULT NULL PRIMARY KEY AUTOINCREMENT,
  'content_type'     INTEGER DEFAULT NULL,
  'entity_guid'      TEXT    DEFAULT NULL,
  'device_guid'      TEXT    DEFAULT NULL,
  'version_guid'     TEXT    DEFAULT NULL,
  'content'          TEXT    DEFAULT NULL,
  'create_date_time' TEXT    DEFAULT NULL
);
*/

use Famoser\SyncApi\Helpers\FormatHelper;
use Famoser\SyncApi\Models\Communication\Entities\CollectionEntity;
use Famoser\SyncApi\Models\Communication\Entities\DeviceEntity;
use Famoser\SyncApi\Models\Communication\Entities\SyncEntity;
use Famoser\SyncApi\Models\Communication\Entities\UserEntity;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;
use Famoser\SyncApi\Types\ContentType;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseEntity as SyncBaseEntity;

class ContentVersion extends BaseEntity
{
    /* @var int $content_type const_of:ContentType */
    public $content_type;

    /* @var string $entity_guid type_of:guid */
    public $entity_guid;

    /* @var string $device_guid type_of:guid */
    public $device_guid;

    /* @var string $version_guid type_of:guid */
    public $version_guid;

    /* @var string $content */
    public $content;

    /* @var int $create_date_time type_of:DateTime */
    public $create_date_time;

    /**
     * create new version for user
     * @param UserEntity $entity
     * @return static
     */
    public static function createNewForUser(UserEntity $entity)
    {
        return static::createNew($entity, ContentType::User);
    }

    /**
     * create new version for device
     * @param DeviceEntity $entity
     * @return static
     */
    public static function createNewForDevice(DeviceEntity $entity)
    {
        return static::createNew($entity, ContentType::Device);
    }

    /**
     * create new version for collection
     * @param CollectionEntity $entity
     * @return static
     */
    public static function createNewForCollection(CollectionEntity $entity)
    {
        return static::createNew($entity, ContentType::Collection);
    }

    /**
     * create new version for entity
     * @param SyncEntity $entity
     * @return static
     */
    public static function createNewForEntity(SyncEntity $entity)
    {
        return static::createNew($entity, ContentType::Entity);
    }

    /**
     * creates a new instance of this class and fills out all available properties
     * @param SyncBaseEntity $entity
     * @param $contentType
     * @return static
     */
    private static function createNew(SyncBaseEntity $entity, $contentType)
    {
        $content = new static();
        $content->content_type = $contentType;
        $content->entity_guid = $entity->Id;
        $content->version_guid = $entity->VersionId;
        $content->content = $entity->Content;
        $content->create_date_time = time();
        return $content;
    }

    /**
     * create UserEntity from this instance
     * @param User $user
     * @return UserEntity
     */
    public function createUserEntity(User $user)
    {
        $entity = new UserEntity();
        $entity->Identifier = $user->identifier;
        $entity->Id = $user->guid;

        $entity->PersonalSeed = $user->personal_seed;

        $this->writeToEntity($entity);

        return $entity;
    }

    /**
     * create DeviceEntity from this instance
     * @param Device $device
     * @return DeviceEntity
     */
    public function createDeviceEntity(Device $device)
    {
        $entity = new DeviceEntity();
        $entity->Identifier = $device->identifier;
        $entity->Id = $device->guid;

        $entity->UserId = $device->user_guid;

        $this->writeToEntity($entity);

        return $entity;
    }

    /**
     * create CollectionEntity from this instance
     * @param Collection $collection
     * @return CollectionEntity
     */
    public function createCollectionEntity(Collection $collection)
    {
        $entity = new CollectionEntity();
        $entity->Identifier = $collection->identifier;
        $entity->Id = $collection->guid;

        $entity->UserId = $collection->user_guid;
        $entity->DeviceId = $this->device_guid;

        $this->writeToEntity($entity);

        return $entity;
    }

    /**
     * create SyncEntity from this instance
     * @param Entity $ent
     * @return SyncEntity
     */
    public function createSyncEntity(Entity $ent)
    {
        $entity = new SyncEntity();
        $entity->Identifier = $ent->identifier;
        $entity->Id = $ent->guid;

        $entity->UserId = $ent->user_guid;
        $entity->DeviceId = $this->device_guid;
        $entity->CollectionId = $ent->collection_guid;

        $this->writeToEntity($entity);

        return $entity;
    }

    /**
     * write available properties into BaseEntity
     * @param SyncBaseEntity $entity
     */
    private function writeToEntity(SyncBaseEntity $entity)
    {
        $entity->Content = $this->content;
        $entity->CreateDateTime = FormatHelper::toCSharpDateTime($this->create_date_time);
        $entity->VersionId = $this->version_guid;
    }

    public function getTableName()
    {
        return "content_versions";
    }
}