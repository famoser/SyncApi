<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:05
 */

namespace Famoser\SyncApi\Models\Communication\Entities\Base;


/**
 * an base entity to be overridden and used to specify basic properties which every transferred entity contains
 * @package Famoser\SyncApi\Models\Communication\Entities\Base
 */
class BaseCommunicationEntity
{
    /* @var string $Id type_of:guid*/
    public $Id;
    
    /* @var string $VersionId type_of:guid*/
    public $VersionId;

    /* @var int $OnlineAction const_of:OnlineAction*/
    public $OnlineAction;

    /* @var string $Content */
    public $Content;

    /* @var \DateTime $CreateDateTime */
    public $CreateDateTime;
    
    /* @var string $Identifier */
    public $Identifier;
}
