<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:08
 */

namespace Famoser\SyncApi\Models\Communication\Entities;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\TextProperty;


/**
 * a transferred collection entity
 * this entity is the actual content the user wants to save, it belongs to a collection hence it must specify the user
 *
 * @package Famoser\SyncApi\Models\Communication\Entities
 */
class SyncCommunicationEntity extends CollectionCommunicationEntity
{
    /* @var string $CollectionId type_of:guid */ 
    public $CollectionId;

    /**
     * gets the json properties needed to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties()
    {
        $props = parent::getJsonProperties();
        $props['CollectionId'] = new TextProperty('CollectionId');
        return $props;
    }
}
