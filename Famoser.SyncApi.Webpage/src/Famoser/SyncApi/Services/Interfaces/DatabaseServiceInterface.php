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
     * @param string $where
     * @param null $parameters
     * @param null|string $orderBy
     * @param int $limit
     * @param string $selector
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|Entity[]|FrontendUser[]
     * |User[]|UserCollection[]|bool
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
     * @param string $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return int
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
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|Entity[]|FrontendUser[]
     * |User[]|UserCollection[]|bool
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
     * @param string $where
     * @param null $parameters
     * @param string $orderBy
     * @return BaseEntity
     * |User|UserCollection|bool
     */
    public function getSingleFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null);

    /**
     * get the first entry from the database which matches the conditions
     *
     * @param BaseEntity $entity
     * @param $id
     * @return Application|ApplicationSetting|AuthorizationCode|Collection|ContentVersion|Device|Entity|FrontendUser
     * |User|UserCollection|bool
     */
    public function getSingleByIdFromDatabase(BaseEntity $entity, $id);

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
     * @param null $arr
     * @return bool
     */
    public function execute($sql, $arr = null);

    /**
     * execute the specified sql query, return the FETCH_NUM result
     *
     * @param $sql
     * @param null $arr
     * @return bool
     */
    public function executeAndCount($sql, $arr = null);

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