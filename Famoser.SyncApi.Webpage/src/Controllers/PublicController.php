<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 07/06/2016
 * Time: 17:54
 */

namespace Famoser\SyncApi\Controllers;


use Famoser\SyncApi\Models\ApiConfiguration;
use PHPQRCode\Constants;
use PHPQRCode\QRcode;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicController extends BaseController
{
    public function index(Request $request, Response $response, $args)
    {
        $this->createImageIfNecessary();
        return $this->renderTemplate($response, "index", $args);
    }

    public function indexAsJson(Request $request, Response $response, $args)
    {
        $infos = $this->getApiInformations();
        return $response->withJson($infos);
    }

    private function getApiInformations()
    {
        $apiConfig = new ApiConfiguration();
        $apiConfig->GenerationKeyInterations = 10000;
        $apiConfig->Uri = "https://api.masspass.famoser.ch/1.0/";
        $apiConfig->InitialisationVector = [129, 104, 236, 64, 22, 129, 76, 125, 122, 223, 214, 33, 238, 180, 218, 126]; // generate 16 here: https://www.random.org/cgi-bin/randbyte?nbytes=16&format=d
        $apiConfig->GenerationKeyLenghtInBytes = 32;
        $apiConfig->GenerationSalt = [136, 249, 54, 92, 129, 200, 62, 128, 178, 33, 220, 177, 148, 172, 180, 223, 10, 113, 167, 206, 97, 163, 45, 228]; //generate 24 here: https://www.random.org/cgi-bin/randbyte?nbytes=24&format=d
        return $apiConfig;
    }

    private function createImageIfNecessary()
    {
        $imgPath = $this->container->get("settings")["public_path"] . '/images/apiInfos.png';
        if (!file_exists($imgPath)) {
            $qrContent = json_encode($this->getApiInformations());
            QRcode::png($qrContent, $imgPath, Constants::QR_ECLEVEL_M, 10, 1);
        }
    }
}