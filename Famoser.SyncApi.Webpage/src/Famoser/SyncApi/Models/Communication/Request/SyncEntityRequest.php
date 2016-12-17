<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:17
 */

namespace Famoser\SyncApi\Models\Communication\Request;

use Famoser\SyncApi\Framework\Json\Models\ArrayProperty;
use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\ObjectProperty;
use Famoser\SyncApi\Models\Communication\Entities\SyncCommunicationEntity;
use Famoser\SyncApi\Models\Communication\Request\Base\BaseRequest;

/**
 * the entities to be synced
 * @package Famoser\SyncApi\Models\Communication\Request
 */
class SyncEntityRequest extends BaseRequest
{
    /* @var SyncCommunicationEntity[] $SyncEntities */
    public $SyncEntities;

    /**
     * gets the json properties needed to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties()
    {
        $arr = parent::getJsonProperties();
        $arr['SyncEntities'] = new ArrayProperty(
            'SyncEntities',
            new ObjectProperty('SyncEntities', new SyncCommunicationEntity())
        );
        return $arr;
    }
}
