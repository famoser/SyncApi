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
use Interop\Container\ContainerInterface;
use PDO;

class DatabaseHelper
{
    /*
     * @var \PDO
     */
    private $database;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initializeDatabase();
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

    private function initializeDatabase()
    {
        $activePath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db']["path"];

        if (!file_exists($activePath)) {
            copy(
                $activePath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db']["template_path"],
                $activePath = $this->container["settings"]["data_path"] . "/" . $this->container['settings']['db']["path"]
            );
        }

        $this->database = $this->constructPdo($activePath);
    }

    private function constructPdo($path)
    {
        $pdo = new PDO("sqlite:" . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    private function createQuery(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000, $selector = "*")
    {
        $sql = "SELECT " . $selector . " FROM " . $entity->getTableName();
        if ($where != null) {
            $sql .= " WHERE " . $where;
        }
        if ($orderBy != null) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $sql .= " LIMIT " . $limit;
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
            LogHelper::log($sql . "     " . json_encode($parameters), "DatabaseHelper" . uniqid() . ".txt");
            $request = $this->getConnection()->prepare($sql);
            if (!$request->execute($parameters)) {
                return false;
            }
            return $request->fetchAll(PDO::FETCH_CLASS, get_class($entity));
        } catch (\Exception $ex) {
            LogHelper::log($ex->getMessage() . "     " . $ex->getTraceAsString() . "     " . $sql . "     " . json_encode($parameters), "DatabaseHelper.txt");
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
     * @return bool|\Famoser\SyncApi\Models\Entities\Application[]|\Famoser\SyncApi\Models\Entities\ApplicationSetting[]|\Famoser\SyncApi\Models\Entities\AuthorizationCode[]|\Famoser\SyncApi\Models\Entities\Collection[]|\Famoser\SyncApi\Models\Entities\ContentVersion[]|\Famoser\SyncApi\Models\Entities\Device[]|\Famoser\SyncApi\Models\Entities\Entity[]|\Famoser\SyncApi\Models\Entities\FrontendUser[]|\Famoser\SyncApi\Models\Entities\User[]|\Famoser\SyncApi\Models\Entities\UserCollection[]
     */
    public function getFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000, $selector = "*")
    {
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit, $selector);
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
    public function countFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
    {
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit, "COUNT(*)");
        $res = $this->execute($sql, $parameters);
        return $res;
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
     * @return Application[]|ApplicationSetting[]|AuthorizationCode[]|Collection[]|ContentVersion[]|Device[]|Entity[]|FrontendUser[]|User[]|UserCollection[]|bool
     */
    public function getWithInFromDatabase(BaseEntity $entity, $property, $values, $invertIn = false, $where = null, $parameters = null, $orderBy = null, $limit = 1000)
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
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, $limit);
        $res = $this->executeAndFetch($entity, $sql, $parameters);
        return $res;
    }

    /**
     * @param BaseEntity $entity
     * @param null $where
     * @param null $parameters
     * @param null $orderBy
     * @return Application|ApplicationSetting|AuthorizationCode|Collection|ContentVersion|Device|Entity|FrontendUser|User|UserCollection|bool
     */
    public function getSingleFromDatabase(BaseEntity $entity, $where = null, $parameters = null, $orderBy = null)
    {
        $sql = $this->createQuery($entity, $where, $parameters, $orderBy, 1);
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
        LogHelper::log(json_encode($properties, JSON_PRETTY_PRINT) . "\n\n\n" . json_encode($entity, JSON_PRETTY_PRINT), "DatabaseHelper_" . $entity->getTableName() . '_' . time() . "_" . uniqid() . ".txt");
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
     * @param $arr
     * @return bool
     */
    public function execute($sql, $arr = null)
    {
        $prep = $this->getConnection()->prepare($sql);
        return $prep->execute($arr);
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
