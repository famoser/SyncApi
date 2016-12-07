<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 04/12/2016
 * Time: 12:55
 */

namespace Famoser\SyncApi\Tests;


use Psr\Http\Message\ResponseInterface;

/**
 * helps asserting properties of response
 *
 * @package Famoser\SyncApi\Tests
 */
class AssertHelper
{
    /**
     * extract the response string from a response object
     *
     * @param ResponseInterface $response
     * @return string
     */
    public static function getResponseString(ResponseInterface $response)
    {
        $response->getBody()->rewind();
        return $response->getBody()->getContents();
    }

    /**
     * check if request was successful
     * returns the tested response string
     *
     * @param \PHPUnit_Framework_TestCase $testingUnit
     * @param ResponseInterface $response
     * @return string
     */
    public static function checkForSuccessfulApiResponse(
        \PHPUnit_Framework_TestCase $testingUnit,
        ResponseInterface $response
    )
    {
        //valid status code
        $testingUnit->assertTrue($response->getStatusCode() == 200);

        //no error in json response
        $responseString = static::getResponseString($response);
        $testingUnit->assertContains("\"ApiError\":0", $responseString);
        $testingUnit->assertContains("\"RequestFailed\":false", $responseString);

        return $responseString;
    }

    /**
     * check if request was successful
     * returns the tested response string
     *
     * @param \PHPUnit_Framework_TestCase $testingUnit
     * @param ResponseInterface $response
     * @param int $expectedCode
     * @return string
     */
    public static function checkForFailedApiResponse(
        \PHPUnit_Framework_TestCase $testingUnit,
        ResponseInterface $response,
        $expectedCode = 500
    )
    {
        //valid status code
        $testingUnit->assertTrue($response->getStatusCode() == $expectedCode);


        //no error in json response
        $responseString = static::getResponseString($response);
        $testingUnit->assertNotContains("\"ApiError\":0", $responseString);
        $testingUnit->assertContains("\"RequestFailed\":true", $responseString);

        return $responseString;
    }
}