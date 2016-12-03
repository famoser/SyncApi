<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:15
 */

namespace Famoser\SyncApi\Models\Communication\Request;


use Famoser\SyncApi\Framework\Json\Models\ArrayProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\ObjectProperty;
use Famoser\SyncApi\Models\Communication\Entities\CollectionCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

/**
 * an collection request; is handled by the collection controller and sent to /collections/*
 * @package Famoser\SyncApi\Models\Communication\Request
 */
class CollectionEntityRequest extends BaseRequest
{
    /* @var CollectionCommunicationEntity[] $CollectionEntities */
    public $CollectionEntities;

    /**
     * gets the json properties needed to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties()
    {
        $arr = parent::getJsonProperties();
        $arr["CollectionEntities"] = new ArrayProperty(
            "CollectionEntities",
            new ObjectProperty("CollectionEntities", new CollectionCommunicationEntity())
        );
        return $arr;
    }
}
