<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 03/12/2016
 * Time: 20:13
 */

namespace Famoser\SyncApi\Services\Interfaces;


use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\ApplicationSetting;
use Famoser\SyncApi\Models\Entities\AuthorizationCode;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\Entity;
use Famoser\SyncApi\Models\Entities\FrontendUser;

/**
 * Interface DatabaseServiceInterface
 *
 * @package Famoser\SyncApi\Services\Interfaces
 */
interface DatabaseServiceInterface
{
    /**
     * gets all entities which match the specified conditions from the database
     *
     * @param BaseEntity $entity
     * @param null|string $where
     * @param null|string $orderBy
     * @param null|array $parameters
     * @param int $limit
     * @param string $selector
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|
     * Entity[]|FrontendUser[]|User[]|UserCollection[]
     */
    public function getFromDatabase(
        BaseEntity $entity,
        $where = null,
        $parameters = null,
        $orderBy = null,
        $limit = -1,
        $selector = '*'
    );

    /**
     * counts the entities which match the conditions
     *
     * @param BaseEntity $entity
     * @param null|string $where
     * @param null|string $orderBy
     * @param null|array $parameters
     * @param int $limit
     * @return false|int
     */
    public function countFromDatabase(
        BaseEntity $entity,
        $where = null,
        $parameters = null,
        $orderBy = null,
        $limit = -1
    );

    /**
     * gets all entities whose property is one of the values provided and which match the specified conditions
     *
     * @param BaseEntity $entity
     * @param string $property
     * @param int[] $values
     * @param bool $invertIn
     * @param null|string $where
     * @param null|string $orderBy
     * @param null|array $parameters
     * @param int $limit
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|
     * Entity[]|FrontendUser[]|User[]|UserCollection[]
     */
    public function getWithInFromDatabase(
        BaseEntity $entity,
        $property,
        $values,
        $invertIn = false,
        $where = null,
        $parameters = null,
        $orderBy = null,
        $limit = -1
    );

    /**
     * get the first entry from the database which matches the conditions
     *
     * @param BaseEntity $entity
     * @param null|string $where
     * @param null|array $parameters
     * @param null|string $orderBy
     * @return null|Application|ApplicationSetting|AuthorizationCode|Collection|ContentVersion|Device|Entity|
     * FrontendUser|User|UserCollection
     */
    public function getSingleFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null);

    /**
     * get the first entry from the database which matches the conditions
     *
     * @param BaseEntity $entity
     * @param int $entityId
     * @return null|Application|ApplicationSetting|AuthorizationCode|Collection|ContentVersion|Device|Entity|
     * FrontendUser|User|UserCollection
     */
    public function getSingleByIdFromDatabase(BaseEntity $entity, $entityId);

    /**
     * save the entity to the database
     * if the entity was retrieved from the database before, it will replace the old data
     *
     * @param BaseEntity $entity
     * @return bool
     */
    public function saveToDatabase(BaseEntity $entity);

    /**
     * execute the specified sql query, return if the query was successful
     *
     * @param string $sql
     * @param null|array $parameters
     * @return bool
     */
    public function execute($sql, $parameters = null);

    /**
     * execute the specified sql query, return the FETCH_NUM result
     *
     * @param $sql
     * @param null|array $parameters
     * @return false|int
     */
    public function executeAndCount($sql, $parameters = null);

    /**
     * deletes the entity from the database
     *
     * @param BaseEntity $entity
     * @return bool
     */
    public function deleteFromDatabase(BaseEntity $entity);

    /**
     * frees up any resources / files locks
     * behaviour of service calls after disposing it is undefined
     * @return void
     */
    public function dispose();
}