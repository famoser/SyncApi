<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 22:40
 */

session_start();

use Famoser\SyncApi\Middleware\LoggingMiddleware;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Services\LoggerService;
use Famoser\SyncApi\Services\RequestService;
use Famoser\SyncApi\SyncApiApp;
use Famoser\SyncApi\Types\FrontendError;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Container;

require '../../vendor/autoload.php';

$app = new SyncApiApp(
    [
        'displayErrorDetails' => true,
        'debug_mode' => true,
        'api_modulo' => 10000019,
        'db_path' => realpath("../../app/data.sqlite"),
        'db_template_path' => realpath("../../app/data_template.sqlite"),
        'file_path' => realpath("../../app/files"),
        'cache_path' => realpath("../../app/cache"),
        'log_path' => realpath("../../app/logs"),
        'template_path' => realpath("../../app/templates"),
        'public_path' => realpath("../public")
    ]
);

$app->run();