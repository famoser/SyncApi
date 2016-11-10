<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 28/05/2016
 * Time: 21:49
 */

namespace Famoser\SyncApi\Middleware;


use Famoser\SyncApi\Helpers\LogHelper;
use Slim\Http\Request;
use Slim\Http\Response;

class LoggingMiddleware extends BaseMiddleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        LogHelper::configure($this->container->get("settings")["log_path"]);
        $files = glob($this->container->get("settings")["log_path"] . '/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }

        $str = $request->getMethod() . ": " . $request->getUri()->getPath() . "\n";
        $jsonObj = $request->getParsedBody();
        if ($jsonObj == null) {
            LogHelper::log($str . $request->getBody(), "Request.txt");
        } else {
            LogHelper::log($str . json_encode($request->getParsedBody(), JSON_PRETTY_PRINT), "Request.txt");
        }

        $response = $next($request, $response);
        return $response;
    }
}
