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
use Famoser\SyncApi\Services\MailService;
use Famoser\SyncApi\Services\RequestService;
use Famoser\SyncApi\Services\SessionService;
use Famoser\SyncApi\Types\ApiError;
use Famoser\SyncApi\Types\FrontendError;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
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

    const DATABASE_SERVICE_KEY = 'databaseService';
    const LOGGING_SERVICE_KEY = 'loggingService';
    const REQUEST_SERVICE_KEY = 'requestService';
    const SESSION_SERVICE_KEY = 'sessionService';
    const MAIL_SERVICE_KEY = 'mailService';

    const SETTINGS_KEY = 'settings';

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
        $this->group('', $this->getWebAppRoutes());
        $this->group('/1.0', $this->getApiRoutes());
    }

    /**
     * override the environment (to mock requests for example)
     *
     * @param Environment $environment
     */
    public function overrideEnvironment(Environment $environment)
    {
        $this->getContainer()['environment'] = $environment;
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
            $this->get('/', $controllerNamespace . 'PublicController:index')->setName('index');
            $this->get('/info', $controllerNamespace . 'PublicController:info')->setName('api_info');

            $this->get('/login', $controllerNamespace . 'LoginController:login')->setName('login');
            $this->post('/login', $controllerNamespace . 'LoginController:loginPost');

            $this->get('/register', $controllerNamespace . 'LoginController:register')->setName('register');
            $this->post('/register', $controllerNamespace . 'LoginController:registerPost');

            $this->get('/forgot', $controllerNamespace . 'LoginController:forgot')->setName('forgot');
            $this->post('/forgot', $controllerNamespace . 'LoginController:forgotPost');

            $this->get('/recover', $controllerNamespace . 'LoginController:recover')->setName('recover');
            $this->post('/recover', $controllerNamespace . 'LoginController:recoverPost');

            $this->group(
                '/dashboard',
                function () use ($controllerNamespace) {
                    $this->get('/', $controllerNamespace . 'ApplicationController:index')
                        ->setName('application_index');
                    $this->get('/show/{id}', $controllerNamespace . 'ApplicationController:show')
                        ->setName('application_show');

                    $this->get('/new', $controllerNamespace . 'ApplicationController:create')
                        ->setName('application_new');
                    $this->post('/new', $controllerNamespace . 'ApplicationController:createPost');

                    $this->get('/edit/{id}', $controllerNamespace . 'ApplicationController:edit')
                        ->setName('application_edit');
                    $this->post('/edit/{id}', $controllerNamespace . 'ApplicationController:editPost');

                    $this->get('/settings/{id}', $controllerNamespace . 'ApplicationController:settings')
                        ->setName('application_settings');
                    $this->post('/settings/{id}', $controllerNamespace . 'ApplicationController:settingsPost');

                    $this->get('/delete/{id}', $controllerNamespace . 'ApplicationController:remove')
                        ->setName('application_delete');
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
                '/auth',
                function () use ($controllerNamespace) {
                    $this->post('/use', $controllerNamespace . 'AuthorizationController:useCode');
                    $this->post('/generate', $controllerNamespace . 'AuthorizationController:generate');
                    $this->post('/sync', $controllerNamespace . 'AuthorizationController:sync');
                    $this->post('/status', $controllerNamespace . 'AuthorizationController:status');
                }
            );

            $this->group(
                '/users',
                function () use ($controllerNamespace) {
                    $this->post('/auth', $controllerNamespace . 'UserController:auth');
                }
            );

            $this->group(
                '/devices',
                function () use ($controllerNamespace) {
                    $this->post('/get', $controllerNamespace . 'DeviceController:get');
                    $this->post('/auth', $controllerNamespace . 'DeviceController:auth');
                    $this->post('/unauth', $controllerNamespace . 'DeviceController:unAuth');
                }
            );

            $this->group(
                '/collections',
                function () use ($controllerNamespace) {
                    $this->post('/sync', $controllerNamespace . 'CollectionController:sync');
                }
            );

            $this->group(
                '/entities',
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
        $container = new Container($configuration);

        //add handlers & services
        $this->addHandlers($container);
        $this->addServices($container);

        //add view
        $container['view'] = function (Container $container) {
            $view = new Twig(
                $container->get(SyncApiApp::SETTINGS_KEY)['template_path'],
                [
                    'cache' => $container->get(SyncApiApp::SETTINGS_KEY)['cache_path'],
                    'debug' => $container->get(SyncApiApp::SETTINGS_KEY)['debug_mode']
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

        return $container;
    }

    /**
     * add the error handlers to the container
     *
     * @param Container $container
     */
    private function addHandlers(Container $container)
    {
        $errorHandler = $this->createErrorHandlerClosure($container);

        //third argument: \Throwable
        $container['phpErrorHandler'] = $errorHandler;
        //third argument: \Exception
        $container['errorHandler'] = $errorHandler;

        $container['notAllowedHandler'] = $this->createNotFoundHandlerClosure($container, ApiError::METHOD_NOT_ALLOWED);
        $container['notFoundHandler'] = $this->createNotFoundHandlerClosure($container, ApiError::NODE_NOT_FOUND);
    }

    /**
     * checks if a specific request is done by the api library
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function isApiRequest(ServerRequestInterface $request)
    {
        return strpos($request->getUri()->getPath(), '/1.0/') === 0 && $request->getMethod() == 'POST';
    }

    /**
     * creates a closure which has no third argument
     *
     * @param ContainerInterface $container
     * @param $apiError
     * @return \Closure
     */
    private function createNotFoundHandlerClosure(ContainerInterface $container, $apiError)
    {
        return function () use ($container, $apiError) {
            return function (ServerRequestInterface $request, ResponseInterface $response) use ($container, $apiError) {
                if ($this->isApiRequest($request)) {
                    $resp = new BaseResponse();
                    $resp->RequestFailed = true;
                    $resp->ApiError = $apiError;
                    $resp->ServerMessage = ApiError::toString($apiError);
                    return $container['response']->withStatus(500)->withJson($resp);
                }
                return $container['view']->render($response, 'public/not_found.html.twig', []);
            };
        };
    }

    /**
     * creates a closure which accepts \Exception and \Throwable as third argument
     *
     * @param ContainerInterface $cont
     * @return \Closure
     */
    private function createErrorHandlerClosure(ContainerInterface $cont)
    {
        return function () use ($cont) {
            return function (ServerRequestInterface $request, ResponseInterface $response, $error = null) use ($cont) {
                if ($error instanceof \Exception || $error instanceof \Throwable) {
                    $errorString = $error->getFile() . ' (' . $error->getLine() . ')\n' .
                        $error->getCode() . ': ' . $error->getMessage() . '\n' .
                        $error->getTraceAsString();
                } else {
                    $errorString = 'unknown error type occurred :/. Details: ' . print_r($error);
                }

                $cont[SyncApiApp::LOGGING_SERVICE_KEY]->log(
                    $errorString,
                    'exception.txt'
                );

                //return json if api request
                if ($this->isApiRequest($request)) {
                    $resp = new BaseResponse();
                    $resp->RequestFailed = true;
                    if ($error instanceof ApiException) {
                        $resp->ApiError = $error->getCode();
                    } else {
                        $resp->ApiError = ApiError::SERVER_ERROR;
                    }
                    $resp->ServerMessage = $errorString;
                    return $cont['response']->withStatus(500)->withJson($resp);
                } else {
                    //behaviour for FrontendExceptions
                    if ($error instanceof FrontendException) {
                        //tried to access page where you need to be logged in
                        if ($error->getCode() == FrontendError::NOT_LOGGED_IN) {
                            $reqUri = $request->getUri()->withPath($cont->get('router')->pathFor('login'));
                            return $cont['response']->withStatus(403)->withRedirect($reqUri);
                        }
                    }

                    //general error page
                    $args = [];
                    $args['error'] = $errorString;
                    return $cont['view']->render($response, 'public/server_error.html.twig', $args);
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
        $container[SyncApiApp::LOGGING_SERVICE_KEY] = function (Container $container) {
            return new LoggingService($container);
        };
        $container[SyncApiApp::REQUEST_SERVICE_KEY] = function (Container $container) {
            return new RequestService($container);
        };
        $container[SyncApiApp::DATABASE_SERVICE_KEY] = function (Container $container) {
            return new DatabaseService($container);
        };
        $container[SyncApiApp::SESSION_SERVICE_KEY] = function (Container $container) {
            return new SessionService($container);
        };
        $container[SyncApiApp::MAIL_SERVICE_KEY] = function (Container $container) {
            return new MailService($container);
        };
    }
}
