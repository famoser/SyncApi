<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:06
 */

namespace Famoser\SyncApi\Models\Communication\Entities;
use Famoser\SyncApi\Framework\Json\Models\Base\AbstractJsonProperty;
use Famoser\SyncApi\Framework\Json\Models\TextProperty;


/**
 * a transferred collection.
 * @package Famoser\SyncApi\Models\Communication\Entities
 */
class CollectionCommunicationEntity extends DeviceCommunicationEntity
{
    /* @var string $DeviceId type_of:guid */
    public $DeviceId;

    /**
     * gets the json properties needed to deserialize
     *
     * @return AbstractJsonProperty[]
     */
    public function getJsonProperties()
    {
        $props = parent::getJsonProperties();
        $props['DeviceId'] = new TextProperty('DeviceId');
        return $props;
    }
}
