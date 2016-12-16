<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 22:40
 */

session_start();

use Famoser\SyncApi\SyncApiApp;

$ds = DIRECTORY_SEPARATOR;
$oneUp = ".." . $ds;
$basePath = realpath(__DIR__ . "/" . $oneUp . $oneUp) . $ds;

$debugModel = !file_exists(".prod");

require '../../vendor/autoload.php';

$app = new SyncApiApp(
    [
        'displayErrorDetails' => $debugModel,
        'debug_mode' => $debugModel,
        'api_modulo' => 10000019,
        'db_path' => $basePath . "app/data.sqlite",
        'db_template_path' => $basePath . "app/data_template.sqlite",
        'file_path' => $basePath . "app/files",
        'cache_path' => $basePath . "app/cache",
        'log_path' => $basePath . "app/logs",
        'template_path' => $basePath . "app/templates",
        'public_path' => $basePath . "src/public"
    ]
);

$app->run();