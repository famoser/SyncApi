<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:06
 */

namespace Famoser\SyncApi\Models\Communication\Entities;


use Famoser\SyncApi\Framework\Json\Models\Base\JsonValueProperty;
use Famoser\SyncApi\Framework\Json\Models\TextProperty;
use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;

/**
 * a transferred device entity
 * contains the user id the device belongs to
 * @package Famoser\SyncApi\Models\Communication\Entities
 */
class DeviceCommunicationEntity extends BaseCommunicationEntity
{
    /* @var string $UserId type_of:guid */
    public $UserId;

    /**
     * gets the json properties needed to deserialize
     *
     * @return JsonValueProperty[]
     */
    public function getJsonProperties()
    {
        $props = parent::getJsonProperties();
        $props["UserId"] = new TextProperty("UserId");
        return $props;
    }
}
