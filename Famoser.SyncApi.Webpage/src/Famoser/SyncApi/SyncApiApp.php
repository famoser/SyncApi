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
use Famoser\SyncApi\Middleware\LoggingMiddleware;
use Famoser\SyncApi\Models\Communication\Response\Base\BaseResponse;
use Famoser\SyncApi\Services\DatabaseService;
use Famoser\SyncApi\Services\LoggingService;
use Famoser\SyncApi\Services\RequestService;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\FrontendError;
use InvalidArgumentException;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

/**
 * the sync api application, in one neat class :)
 *
 * @package Famoser\SyncApi
 */
class SyncApiApp extends App
{
    private $controllerNamespace = 'Famoser\SyncApi\Controllers\\';

    const DATABASE_SERVICE_KEY = "databaseService";
    const LOGGING_SERVICE_KEY = "loggingService";
    const REQUEST_SERVICE_KEY = "requestService";

    const SETTINGS_KEY = "settings";

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
                'displayErrorDetails' => false,
                'debug_mode' => false
            ],
            $configuration
        );

        //construct parent with container
        parent::__construct(
            $this->constructContainer(
                [
                    SyncApiApp::SETTINGS_KEY => $configuration
                ]
            )
        );

        //add middleware
        $this->add(new LoggingMiddleware($this->getContainer()));

        //add routes
        $this->group("", $this->getWebAppRoutes());
        $this->group("/1.0", $this->getApiRoutes());
    }

    /**
     * override the environment (to mock requests for example)
     *
     * @param Environment $environment
     */
    public function overrideEnvironment(Environment $environment)
    {
        $this->getContainer()["environment"] = $environment;
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
                    $this->post('/status', $controllerNamespace . 'AuthorizationController:status');
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

        //add handlers & services
        $this->addHandlers($c);
        $this->addServices($c);

        //add view
        $c["view"] = function (Container $container) {
            $view = new Twig(
                $container->get(SyncApiApp::SETTINGS_KEY)["template_path"],
                [
                    'cache' => $container->get(SyncApiApp::SETTINGS_KEY)["cache_path"],
                    'debug' => $container->get(SyncApiApp::SETTINGS_KEY)["debug_mode"]
                ]
            );
            $view->addExtension(
                new TwigExtension(
                    $container['router'],
                    $container['request']->getUri()
                )
            );

            return $view;
        };

        return $c;
    }

    /**
     * add the error handlers to the container
     *
     * @param Container $container
     */
    private function addHandlers(Container $container)
    {
        $container["notFoundHandler"] = function (Container $container) {
            return function (Request $req, Response $resp) use ($container) {
                return $resp->withStatus(404);
            };
        };
        $container["notAllowedHandler"] = function (Container $container) {
            return function (Request $req, Response $resp) use ($container) {
                return $resp->withStatus(405);
            };
        };
        $container["errorHandler"] = function (Container $container) {
            return function (Request $request, Response $response, \Exception $exception) use ($container) {
                $container[SyncApiApp::LOGGING_SERVICE_KEY]->log(
                    $exception->getFile() . " (" . $exception->getLine() . ")\n" .
                    $exception->getCode() . ": " . $exception->getMessage() . "\n" .
                    $exception->getTraceAsString(),
                    "exception.txt"
                );
                //return json if api request
                if (strpos($request->getUri()->getPath(), "/1.0/") === 0 && $request->getMethod() == "POST") {
                    $resp = new BaseResponse();
                    $resp->RequestFailed = true;
                    if ($exception instanceof ApiException) {
                        $resp->ApiError = $exception->getCode();
                    } else {
                        $resp->ApiError = ApiError::SERVER_ERROR;
                    }
                    $resp->ServerMessage = $exception->getMessage();
                    return $container['response']->withStatus(500)->withJson($resp);
                } else {
                    //behaviour for FrontendExceptions
                    if ($exception instanceof FrontendException) {
                        //tried to access page where you need to be logged in
                        if ($exception->getCode() == FrontendError::NOT_LOGGED_IN) {
                            $reqUri = $request->getUri()->withPath($container->get("router")->pathFor("login"));
                            return $container['response']->withRedirect($reqUri);
                        }
                    }

                    //general error page
                    $args = [];
                    $args["error"] = $exception->getMessage();
                    return $container["view"]->render($response, "public/server_error.html.twig", $args);
                }
            };
        };
    }

    /**
     * add all services to the container
     *
     * @param Container $container
     */
    private function addServices(Container $container)
    {
        $container[SyncApiApp::LOGGING_SERVICE_KEY] = function (Container $c) {
            return new LoggingService($c);
        };
        $container[SyncApiApp::REQUEST_SERVICE_KEY] = function (Container $c) {
            return new RequestService($c);
        };
        $container[SyncApiApp::DATABASE_SERVICE_KEY] = function (Container $c) {
            return new DatabaseService($c);
        };
    }
}
