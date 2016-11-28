<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/11/2016
 * Time: 19:10
 */

namespace Famoser\SyncApi;


use Famoser\SyncApi\Exceptions\ApiException;
use Famoser\SyncApi\Exceptions\FrontendException;
use Famoser\SyncApi\Exceptions\ServerException;
use Famoser\SyncApi\Middleware\LoggingMiddleware;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Services\LoggerService;
use Famoser\SyncApi\Services\RequestService;
use Famoser\SyncApi\Types\FrontendError;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class SyncApiApp extends App
{
    private $controllerNamespace = 'Famoser\SyncApi\Controllers\\';

    /**
     * Create new application
     *
     * @param array $configuration an associative array of app settings
     * @throws InvalidArgumentException when no container is provided that implements ContainerInterface
     */
    public function __construct($configuration)
    {
        //$configuration
        $configuration = array_merge(
            [
                'settings' =>
                    [
                        'displayErrorDetails' => false,
                        'debug_mode' => false
                    ]
            ],
            $configuration
        );

        //construct parent with container
        parent::__construct($this->constructContainer($configuration));

        //get middleware
        $this->add(new LoggingMiddleware($this->getContainer()));

        //get routes
        $this->group("", $this->getWebAppRoutes());
        $this->group("/1.0", $this->getApiRoutes());
    }

    /**
     * override a service from the container
     *
     * @param $key
     * @param \Closure $val
     */
    private function overrideContainer($key, \Closure $val)
    {
        $this->getContainer()[$key] = $val;
    }

    /**
     * get the web app routes
     *
     * @return \Closure
     */
    private function getWebAppRoutes()
    {
        $controllerNamespace = $this->controllerNamespace;
        return function () use ($controllerNamespace) {
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
                    $this->get('/', $controllerNamespace . 'ApplicationController:index')
                        ->setName("application_index");
                    $this->get('/show/{id}', $controllerNamespace . 'ApplicationController:show')
                        ->setName("application_show");

                    $this->get('/new', $controllerNamespace . 'ApplicationController:create')
                        ->setName("application_new");
                    $this->post('/new', $controllerNamespace . 'ApplicationController:createPost');

                    $this->get('/edit/{id}', $controllerNamespace . 'ApplicationController:edit')
                        ->setName("application_edit");
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
    }

    /**
     * get the api routes
     *
     * @return \Closure
     */
    private function getApiRoutes()
    {
        $controllerNamespace = $this->controllerNamespace;
        return function () use ($controllerNamespace) {
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
    }

    /**
     * create the container
     *
     * @param $configuration
     * @return Container
     */
    private function constructContainer($configuration)
    {
        $c = new Container($configuration);

        $c["notFoundHandler"] = function (Container $c) {
            return function (Request $req, Response $resp) use ($c) {
                return $resp->withStatus(404);
            };
        };
        $c["notAllowedHandler"] = function (Container $c) {
            return function (Request $req, Response $resp) use ($c) {
                return $resp->withStatus(405);
            };
        };
        $c["errorHandler"] = function (Container $c) {
            return function (Request $request, Response $response, \Exception $exception) use ($c) {
                $c['logger']->log(
                    $exception->getFile() . " (" . $exception->getLine() . ")\n" .
                    $exception->getCode() . ": " . $exception->getMessage() . "\n" .
                    $exception->getTraceAsString(),
                    "exception.txt"
                );
                if ($exception instanceof ServerException) {
                    return $c['response']->withStatus(500)->getBody()->write(
                        "exception occurred: " . $exception->getMessage()
                    );
                } elseif ($exception instanceof ApiException) {
                    $resp = new BaseResponse();
                    $resp->RequestFailed = true;
                    $resp->ApiError = $exception->getCode();
                    $resp->ServerMessage = $exception->getMessage();
                    return $c['response']->withStatus(500)->withJson($resp);
                } elseif ($exception instanceof FrontendException) {
                    if ($exception->getCode() == FrontendError::NOT_LOGGED_IN) {
                        $reqUri = $request->getUri()->withPath($c->get("router")->pathFor("login"));
                        return $c['response']->withRedirect($reqUri);
                    }
                }
                $args = [];
                $args["error"] = $exception->getMessage();
                return $c["view"]->render($response, "public/server_error.html.twig", $args);
            };
        };

        $c["view"] = function (Container $c) {
            $view = new Twig(
                $c->get("settings")["template_path"],
                [
                    'cache' => $c->get("settings")["cache_path"],
                    'debug' => $c->get("settings")["debug_mode"]
                ]
            );
            $view->addExtension(
                new TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                )
            );

            return $view;
        };
        $c["logger"] = function (Container $c) {
            return new LoggerService($c->get("settings")["log_path"]);
        };
        $c["requestService"] = function (Container $c) {
            return new RequestService($c->get("logger"), $c->get("settings")["api_modulo"]);
        };

        return $c;
    }
}