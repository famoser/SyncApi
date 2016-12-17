<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2016
 * Time: 21:49
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Middleware\Base\BaseMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * this middleware initialized logging and the logger; it logs the new request & cleans up data from the old.
 * @package Famoser\SyncApi\Middleware
 */
class LoggingMiddleware extends BaseMiddleware
{
    /**
     * invoke the middleware
     *
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        //delete existing logging files
        $files = glob($this->getLoggingService()->getLogPath() . '/*'); // get all file names
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        //log current request
        $str = $request->getMethod() . ': ' . $request->getUri()->getPath() . '\n';
        $jsonObj = $request->getParsedBody();
        if ($jsonObj == null) {
            $this->getLoggingService()->log(
                $str . $request->getBody(),
                'Request.txt'
            );
        } else {
            $this->getLoggingService()->log(
                $str . json_encode($request->getParsedBody(), JSON_PRETTY_PRINT),
                'Request.txt'
            );
        }

        $response = $next($request, $response);
        return $response;
    }
}
