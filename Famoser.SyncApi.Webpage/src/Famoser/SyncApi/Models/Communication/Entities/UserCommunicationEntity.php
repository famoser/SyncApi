<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04.11.2016
 * Time: 19:07
 */

namespace Famoser\SyncApi\Models\Communication\Entities;


use Famoser\SyncApi\Models\Communication\Entities\Base\BaseCommunicationEntity;

/**
 * a transferred user entity
 * contains a personal seed which is saved to the database at the time it is created, and is not modified afterwards
 *
 * @package Famoser\SyncApi\Models\Communication\Entities
 */
class UserCommunicationEntity extends BaseCommunicationEntity
{
    /* @var string $PersonalSeed */
    public $PersonalSeed;
}
