<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 23.05.2016
 * Time: 15:25
 */

namespace Famoser\SyncApi\Helpers;


use Famoser\SyncApi\Models\Entities\Application;
use Famoser\SyncApi\Models\Entities\ApplicationSetting;
use Famoser\SyncApi\Models\Entities\AuthorizationCode;
use Famoser\SyncApi\Models\Entities\Base\BaseEntity;
use Famoser\SyncApi\Models\Entities\Collection;
use Famoser\SyncApi\Models\Entities\ContentVersion;
use Famoser\SyncApi\Models\Entities\Device;
use Famoser\SyncApi\Models\Entities\Entity;
use Famoser\SyncApi\Models\Entities\FrontendUser;
use Famoser\SyncApi\Models\Entities\User;
use Famoser\SyncApi\Models\Entities\UserCollection;
use Famoser\SyncApi\Services\Interfaces\LoggerInterface;
use Interop\Container\ContainerInterface;
use PDO;

/**
 * the DatabaseHelper allows access to the database. It abstracts sql from logic, and is type safe
 * 
 * @package Famoser\SyncApi\Helpers
 */
class DatabaseHelper
{
    /*
     * @var \PDO
     */
    private $database;

    /* @var ContainerInterface $container */
    private $container;

    /**
     * DatabaseHelper constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->initializeDatabase();
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this->container["logger"];
    }

    /**
     * @return \PDO
     */
    private function getConnection()
    {
        return $this->database;
    }

    /**
     * execute scripts from an .sql file
     *
     * @param $scriptsPath
     */
    public function executeScripts($scriptsPath)
    {
        $files = scandir($scriptsPath);
        foreach ($files as $file) {
            if (substr($file, -3) == "sql") {
                $queries = file_get_contents($scriptsPath . "/" . $file);
                $queryArray = explode(";", $queries);
                foreach ($queryArray as $item) {
                    if (trim($item) != "") {
                        $this->getConnection()->query($item);
                    }
                }
            }
        }
    }

    /**
     * initialize the database
     */
    private function initializeDatabase()
    {
        $dataPath = $this->container["settings"]["data_path"];
        $dbPath = $this->container['settings']['db']["path"];
        $activePath = $dataPath . "/" . $dbPath;

        if (!file_exists($activePath)) {
            $templatePath = $this->container['settings']['db']["template_path"];
            copy(
                $activePath = $dataPath . "/" . $templatePath,
                $activePath = $dataPath . "/" . $dbPath
            );
        }

        $this->database = $this->constructPdo($activePath);
    }

    /**
     * construct a sqlite pdo object from a path
     *
     * @param $path
     * @return PDO
     */
    private function constructPdo($path)
    {
        $pdo = new PDO("sqlite:" . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    /**
     * creates the sql query
     *
     * @param BaseEntity $entity
     * @param null $where
     * @param null $orderBy
     * @param int $limit
     * @param string $selector
     * @return string
     */
    private function createQuery(BaseEntity $entity, $where = null, $orderBy = null, $limit = 1000, $selector = "*")
    {
        $sql = "SELECT " . $selector . " FROM " . $entity->getTableName();
        if ($where != null) {
            $sql .= " WHERE " . $where;
        }
        if ($orderBy != null) {
            $sql .= " ORDER BY " . $orderBy;
        }
        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }
        return $sql;
    }

    /**
     * executes query and fetches all results
     *
     * @param BaseEntity $entity
     * @param $sql
     * @param $parameters
     * @return array|bool|null
     */
    private function executeAndFetch(BaseEntity $entity, $sql, $parameters)
    {
        try {
            $this->getLogger()->log(
                $sql . "     " . json_encode($parameters),
                "DatabaseHelper" . uniqid() . ".txt"
            );
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($parameters)) {
                return false;
            }
            return $request->fetchAll(PDO::FETCH_CLASS, get_class($entity));
        } catch (\Exception $ex) {
            $this->getLogger()->log(
                $ex->getMessage() . "     " . $ex->getTraceAsString() . "     " . $sql . "     " . json_encode($parameters),
                "DatabaseHelper.txt"
            );
        }
        return null;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @param string $selector
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|Entity[]|
     * FrontendUser[]|User[]|UserCollection[]|bool
     */
    public function getFromDatabase(
        BaseEntity $entity, $where = null, $parameters = null,
        $orderBy = null, $limit = -1, $selector = "*")
    {
        $sql = $this->createQuery($entity, $where, $orderBy, $limit, $selector);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        return $res;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return int
     */
    public function countFromDatabase(
        BaseEntity $entity, $where = null, $parameters = null,
        $orderBy = null, $limit = -1)
    {
        $sql = $this->createQuery($entity, $where, $orderBy, $limit, "COUNT(*)");
        return $this->executeAndCount($sql, $parameters);
    }

    /**
     * @param BaseEntity $entity
     * @param string $property
     * @param int[] $values
     * @param bool $invertIn
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @param int $limit
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|Entity[]|
     * FrontendUser[]|User[]|UserCollection[]|bool
     */
    public function getWithInFromDatabase(
        BaseEntity $entity, $property, $values, $invertIn = false,
        $where = null, $parameters = null, $orderBy = null, $limit = -1)
    {
        if ($parameters == null) {
            $parameters = [];
        }
        if ($where == null) {
            $where = " ";
        } else {
            $where .= " AND ";
        }
        $variables = [];
        for ($i = 0; $i < count($values); $i++) {
            $parameters[":" . $property . $i] = $values[$i];
            $variables[] = ":" . $property . $i;
        }
        $where .= $property . (($invertIn) ? " NOT" : "") . " IN (" . implode(",", $variables) . ")";
        $sql = $this->createQuery($entity, $where, $orderBy, $limit);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        return $res;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @return Application|ApplicationSetting|AuthorizationCode|Collection|ContentVersion|Device|Entity|FrontendUser|
     * User|UserCollection|bool
     */
    public function getSingleFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null)
    {
        $sql = $this->createQuery($entity, $where, $orderBy, 1);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        if (count($res) > 0) {
            return $res[0];
        }
        return null;
    }

    /**
     * @param BaseEntity $entity
     * @return bool
     */
    public function saveToDatabase(BaseEntity $entity)
    {
        $properties = (array)$entity;
        $this->getLogger()->log(
            json_encode($properties, JSON_PRETTY_PRINT) . "\n\n\n" . json_encode($entity, JSON_PRETTY_PRINT),
            "DatabaseHelper_" . $entity->getTableName() . '_' . time() . "_" . uniqid() . ".txt"
        );
        unset($properties["id"]);
        if ($entity->id > 0) {
            //update
            $sql = "UPDATE " . $entity->getTableName() . " SET ";
            foreach ($properties as $key => $val) {
                $sql .= $key . "=:" . $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE id=:id";
            $properties = (array)$entity;
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($properties)) {
                return false;
            }
        } else {
            //create
            $sql = "INSERT INTO " . $entity->getTableName() . "(";
            foreach ($properties as $key => $val) {
                $sql .= $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ") VALUES (";
            foreach ($properties as $key => $val) {
                $sql .= ":" . $key . ",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ")";
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($properties)) {
                return false;
            }
            $entity->id = $this->getConnection()->lastInsertId();
        }
        return true;
    }

    /**
     * @param $sql
     * @param null $arr
     * @return bool
     */
    public function execute($sql, $arr = null)
    {
        $prep = $this->getConnection()->prepare($sql);
        return $prep->execute($arr);
    }

    /**
     * @param $sql
     * @param null $arr
     * @return bool
     */
    private function executeAndCount($sql, $arr = null)
    {
        $prep = $this->getConnection()->prepare($sql);
        if (!$prep->execute($arr)) {
            return false;
        }
        return $prep->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * @param BaseEntity $entity
     * @return bool
     */
    public function deleteFromDatabase(BaseEntity $entity)
    {
        $sql = "DELETE FROM " . $entity->getTableName() . " WHERE id=:id";
        $params = array("id" => $entity->id);
        $prep = $this->getConnection()->prepare($sql);
        return $prep->execute($params);
    }
}
