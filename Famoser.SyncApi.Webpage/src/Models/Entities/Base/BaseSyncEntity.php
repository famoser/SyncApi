<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 10/11/2016
 * Time: 21:40
 */

namespace Famoser\SyncApi\Models\Entities\Base;

use Famoser\SyncApi\Models\Communication\Entities\Base\BaseEntity as CommunicationBaseEntity;
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
     * generate content version for a new entity
     *
     * @param CommunicationBaseEntity $entity
     * @return ContentVersion
     */
    public function createContentVersion(CommunicationBaseEntity $entity)
    {
        $content = new ContentVersion();
        $content->content_type = $this->getContentType();
        $content->entity_guid = $entity->Id;
        $content->version_guid = $entity->VersionId;
        $content->content = $entity->Content;
        $content->create_date_time = time();
        return $content;
    }
}