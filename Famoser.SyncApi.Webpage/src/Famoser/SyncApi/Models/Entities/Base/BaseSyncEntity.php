<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/11/2016
 * Time: 21:40
 */

namespace Famoser\SyncApi\Models\Entities\Base;

use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;
use Famoser\SyncApi\Models\Entities\ContentVersion;

abstract class BaseSyncEntity extends BaseEntity
{
    /* @var string $identifier */
    public $identifier;

    /* @var string $guid type_of:guid */
    public $guid;

    /* @var bool $is_deleted */
    public $is_deleted = false;

    /**
     * get the content type for the implementing model
     *
     * @return int
     */
    abstract protected function getContentType();

    /**
     * create the communication entity for the implementing model
     *
     * @return BaseCommunicationEntity
     */
    abstract protected function createSpecificCommunicationEntity();

    /**
     * generate content version for a new entity
     *
     * @param BaseCommunicationEntity $entity
     * @return ContentVersion
     */
    public function createContentVersion(BaseCommunicationEntity $entity)
    {
        $content = new ContentVersion();
        $content->content_type = $this->getContentType();
        $content->entity_guid = $entity->Id;
        $content->version_guid = $entity->VersionId;
        $content->content = $entity->Content;
        $content->create_date_time = time();
        return $content;
    }

    /**
     * creates an entity which can be used by the api for communication
     *
     * @param ContentVersion $version
     * @param $onlineAction
     * @return BaseCommunicationEntity
     */
    public function createCommunicationEntity(ContentVersion $version, $onlineAction)
    {

        $entity = $this->createSpecificCommunicationEntity();
        $entity->Identifier = $this->identifier;
        $entity->Id = $this->guid;

        $entity->Content = $version->content;
        $entity->CreateDateTime = date("c", $version->create_date_time);
        $entity->VersionId = $version->version_guid;
        
        $entity->OnlineAction = $onlineAction;

        return $entity;
    }
}