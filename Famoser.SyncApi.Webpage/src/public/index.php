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
use Famoser\SyncApi\Types\FrontendError;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Container;

require '../../vendor/autoload.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => false,
        'debug_mode' => true,
        'db' => [
            'path' => "data.sqlite",
            'template_path' => "data_template.sqlite"
        ],
        'api_modulo' => 10000019,
        'data_path' => realpath("../../app"),
        'asset_path' => realpath("../Assets"),
        'log_path' => realpath("../../app/logs"),
        'file_path' => realpath("../../app/files"),
        'template_path' => realpath("../../app/templates"),
        'cache_path' => realpath("../../app/cache"),
        'public_path' => realpath("../public")
    ]
];

$c = new Container($configuration);
$c['notFoundHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        return $resp->withStatus(404);
    };
};
$c['notAllowedHandler'] = function (Container $c) {
    return function (Request $req, Response $resp) use ($c) {
        return $resp->withStatus(405);
    };
};
$c['errorHandler'] = function (Container $c) {
    return function (Request $request, Response $response, Exception $exception) use ($c) {
        if ($exception instanceof \Famoser\SyncApi\Exceptions\ServerException) {
            return $response->withStatus(500)->getBody()->write("exception occurred: " . $exception->getMessage());
        } elseif ($exception instanceof \Famoser\SyncApi\Exceptions\ApiException) {
            $resp = new BaseResponse();
            $resp->RequestFailed = true;
            $resp->ApiError = $exception->getCode();
            $resp->ServerMessage = $exception->getMessage();
            return $response->withStatus(500)->withJson($resp);
        } elseif ($exception instanceof \Famoser\SyncApi\Exceptions\FrontendException) {
            if ($exception->getCode() == FrontendError::NOT_LOGGED_IN) {
                $reqUri = $request->getUri()->withPath($c->get("router")->pathFor("login"));
                return $response->withRedirect($reqUri);
            }
        }
        $args = [];
        $args["error"] = $exception->getMessage();
        return $c->get("view")->render($response, "public/server_error.html.twig", $args);
    };
};
// Register component on container
$c['view'] = function (Container $c) {
    $view = new \Slim\Views\Twig(
        $c->get("settings")["template_path"],
        [
            'cache' => $c->get("settings")["cache_path"],
            'debug' => $c->get("settings")["debug_mode"]
        ]
    );
    $view->addExtension(
        new \Slim\Views\TwigExtension(
            $c['router'],
            $c['request']->getUri()
        )
    );

    return $view;
};
$c['logger'] = function (Container $c) {
    return new LoggerService($c->get("settings")["log_path"]);
};
$c['requestService'] = function (Container $c) {
    return new RequestService($c->get("logger"), $c->get("settings")["api_modulo"]);
};

$controllerNamespace = 'Famoser\SyncApi\Controllers\\';

$app = new App($c);
$app->add(new LoggingMiddleware($c));

$apiRoutes = function () use ($controllerNamespace) {
    $this->group(
        "/auth",
        function () use ($controllerNamespace) {
            $this->post('/use', $controllerNamespace . 'AuthorizationController:useCode');
            $this->post('/generate', $controllerNamespace . 'AuthorizationController:generate');
            $this->post('/sync', $controllerNamespace . 'AuthorizationController:sync');
        }
    );

    $this->group(
        "/users",
        function () use ($controllerNamespace) {
            $this->post('/auth', $controllerNamespace . 'UserController:auth');
        }
    );

    $this->group(
        "/devices",
        function () use ($controllerNamespace) {
            $this->post('/get', $controllerNamespace . 'DeviceController:get');
            $this->post('/auth', $controllerNamespace . 'DeviceController:auth');
            $this->post('/unauth', $controllerNamespace . 'DeviceController:unAuth');
        }
    );

    $this->group(
        "/collections",
        function () use ($controllerNamespace) {
            $this->post('/sync', $controllerNamespace . 'CollectionController:sync');
        }
    );

    $this->group(
        "/entities",
        function () use ($controllerNamespace) {
            $this->post('/sync', $controllerNamespace . 'EntityController:sync');
            $this->post('/history/sync', $controllerNamespace . 'EntityController:historySync');
        }
    );
};

$webAppRoutes = function () use ($controllerNamespace) {
    $this->get('/', $controllerNamespace . 'PublicController:index')->setName("index");
    $this->get('/info', $controllerNamespace . 'PublicController:info')->setName("api_info");

    $this->get('/login', $controllerNamespace . 'LoginController:login')->setName("login");
    $this->post('/login', $controllerNamespace . 'LoginController:loginPost');

    $this->get('/register', $controllerNamespace . 'LoginController:register')->setName("register");
    $this->post('/register', $controllerNamespace . 'LoginController:registerPost');

    $this->get('/forgot', $controllerNamespace . 'LoginController:forgot')->setName("forgot");
    $this->post('/forgot', $controllerNamespace . 'LoginController:forgotPost');

    $this->get('/recover/{id}', $controllerNamespace . 'LoginController:recover')->setName("recover");
    $this->post('/recover/{id}', $controllerNamespace . 'LoginController:recoverPost');

    $this->group(
        "/dashboard",
        function () use ($controllerNamespace) {
            $this->get('/', $controllerNamespace . 'ApplicationController:index')->setName("application_index");
            $this->get('/show/{id}', $controllerNamespace . 'ApplicationController:show')->setName("application_show");

            $this->get('/new', $controllerNamespace . 'ApplicationController:create')->setName("application_new");
            $this->post('/new', $controllerNamespace . 'ApplicationController:createPost');

            $this->get('/edit/{id}', $controllerNamespace . 'ApplicationController:edit')->setName("application_edit");
            $this->post('/edit/{id}', $controllerNamespace . 'ApplicationController:editPost');

            $this->get('/settings/{id}', $controllerNamespace . 'ApplicationController:setting')
                ->setName("application_settings");
            $this->post('/settings/{id}', $controllerNamespace . 'ApplicationController:settingsPost');

            $this->get('/delete/{id}', $controllerNamespace . 'ApplicationController:remove')
                ->setName("application_delete");
            $this->post('/delete/{id}', $controllerNamespace . 'ApplicationController:removePost');
        }
    );
};

$app->group("/1.0", $apiRoutes);
$app->group("", $webAppRoutes);

$app->run();
