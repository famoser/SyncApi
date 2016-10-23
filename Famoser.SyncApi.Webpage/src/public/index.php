<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/05/2016
 * Time: 22:40
 */

use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Helpers\RequestHelper;
use Famoser\SyncApi\Helpers\ResponseHelper;
use Famoser\SyncApi\Middleware\ApiVersionMiddleware;
use Famoser\SyncApi\Middleware\AuthorizationMiddleware;
use Famoser\SyncApi\Middleware\JsonMiddleware;
use Famoser\SyncApi\Middleware\LoggingMiddleware;
use Famoser\SyncApi\Middleware\TestsMiddleware;
use Famoser\SyncApi\Models\Request\Base\ApiRequest;
use Famoser\SyncApi\Models\Request\SyncRequest;
use Famoser\SyncApi\Models\Response\Base\ApiResponse;
use Famoser\SyncApi\Types\ApiErrorTypes;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Container;

require '../../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => false,
        'debug_mode' => true,
        'db' => [
            'path' => "sqlite.db",
            'test_path' => "sqlite_tests.db"
        ],
        'data_path' => realpath("../../app"),
        'asset_path' => realpath("../Assets"),
        'log_path' => realpath("../../app/logs"),
        'file_path' => realpath("../../app/files"),
        'template_path' => realpath("../../app/templates"),
        'cache_path' => realpath("../../app/cache"),
        'public_path' => realpath("../public")
    ],
    'api_settings' => [
        'api_version' => 1,
        'test_mode' => false
    ]
];

$c = new Container($configuration);
$c['notFoundHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::RequestUriInvalid);
        if ($c->get("settings")["debug_mode"])
            $res->ApiMessage = "requested: " . $req->getRequestTarget();

        return $resp->withStatus(404, "endpoint not found")->withJson($res);
    };
};
$c['notAllowedHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::RequestUriInvalid);
        if ($c->get("settings")["debug_mode"])
            $res->ApiMessage = "requested: " . $req->getRequestTarget();

        return $resp->withStatus(405, "wrong method")->withJson($res);
    };
};
$c['errorHandler'] = function (Container $c) {
    /**
     * @param $request
     * @param $response
     * @param $exception
     * @return mixed
     */
    return function (Request $request, Response $response, Exception $exception) use ($c) {
        $res = new ApiResponse(false, ApiErrorTypes::ServerFailure);
        if ($c->get("settings")["debug_mode"])
            $res->ApiMessage = "Exception: " . $exception->getMessage() . " \nStack: " . $exception->getTraceAsString();
        return $response->withStatus(500, $exception->getMessage())->withJson($res);
    };
};
// Register component on container
$c['view'] = function (Container $c) {
    $view = new \Slim\Views\Twig($c->get("settings")["template_path"], [
        'cache' => $c->get("settings")["cache_path"],
        'debug' => $c->get("settings")["debug_mode"]
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));

    return $view;
};

$controllerNamespace = 'Famoser\SyncApi\Controllers\\';

$app = new App($c);
$app->add(new JsonMiddleware());
$app->add(new AuthorizationMiddleware());
$app->add(new ApiVersionMiddleware($c));
$app->add(new TestsMiddleware($c));
$app->add(new LoggingMiddleware($c));

$routes = function () use ($controllerNamespace) {
    $this->group("/authorization", function () use ($controllerNamespace) {
        $this->post('/createuser', $controllerNamespace . 'AuthorizationController:createUser');
        $this->post('/wipeuser', $controllerNamespace . 'AuthorizationController:wipeUser');
        $this->post('/authorize', $controllerNamespace . 'AuthorizationController:authorize');
        $this->post('/status', $controllerNamespace . 'AuthorizationController:status');
        $this->post('/createauthorization', $controllerNamespace . 'AuthorizationController:createAuthorization');
        $this->post('/unauthorize', $controllerNamespace . 'AuthorizationController:unAuthorize');
        $this->post('/authorizeddevices', $controllerNamespace . 'AuthorizationController:authorizedDevices');
    });
    $this->group("/sync", function () use ($controllerNamespace) {
        $this->post('/sync', $controllerNamespace . 'SyncController:sync');
        $this->post('/update', $controllerNamespace . 'SyncController:update');
        $this->post('/readcontententity', $controllerNamespace . 'SyncController:readContentEntity');
        $this->post('/gethistory', $controllerNamespace . 'SyncController:getHistory');
    });
};


$app->group("/tests/1.0", $routes);
$app->group("/1.0", $routes);

$app->get("/1.0/", $controllerNamespace . 'PublicController:index');
$app->post("/1.0/", $controllerNamespace . 'PublicController:indexAsJson');

$app->run();