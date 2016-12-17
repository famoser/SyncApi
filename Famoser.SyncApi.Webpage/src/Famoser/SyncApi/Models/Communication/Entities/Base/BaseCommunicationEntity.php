<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:05
 */

namespace Famoser\SyncApi\Models\Communication\Entities\Base;

use Famoser\SyncApi\Framework\Json\Models\Base\JsonProperty;
use Famoser\SyncApi\Framework\Json\Models\DateTimeProperty;
use Famoser\SyncApi\Framework\Json\Models\DateTimeTextProperty;
use Famoser\SyncApi\Framework\Json\Models\IntProperty;
use Famoser\SyncApi\Framework\Json\Models\TextProperty;
use Famoser\SyncApi\Interfaces\IJsonDeserializable;


/**
 * an base entity to be overridden and used to specify basic properties which every transferred entity contains
 *
 * @package Famoser\SyncApi\Models\Communication\Entities\Base
 */
class BaseCommunicationEntity implements IJsonDeserializable
{
    /* @var string $Id type_of:guid */
    public $Id;

    /* @var string $DeviceId type_of:guid */
    public $DeviceId;

    /* @var string $VersionId type_of:guid */
    public $VersionId;

    /* @var int $OnlineAction const_of:OnlineAction */
    public $OnlineAction;

    /* @var string $Content */
    public $Content;

    /* @var string $CreateDateTime type_of:datetime */
    public $CreateDateTime;

    /* @var string $Identifier */
    public $Identifier;

    /**
     * gets the json properties needed to deserialize
     *
     * @return JsonProperty[]
     */
    public function getJsonProperties()
    {
        $props = [];
        $props['Id'] = new TextProperty('Id');
        $props['VersionId'] = new TextProperty('VersionId');
        $props['OnlineAction'] = new IntProperty('OnlineAction');
        $props['Content'] = new TextProperty('Content');
        $props['CreateDateTime'] = new DateTimeTextProperty('CreateDateTime');
        $props['Identifier'] = new TextProperty('Identifier');
        return $props;
    }
}
