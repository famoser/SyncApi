<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 17.12.2016
 * Time: 13:56
 */

namespace Famoser\SyncApi\Tests\TestHelpers\TestApp;


use Famoser\SyncApi\SyncApiApp;

class TestSyncApiApp extends SyncApiApp
{
    /**
     * makes application execution silent (no output in phpUnit console)
     * @param bool $silent
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run($silent = false)
    {
        return parent::run(true);
    }
}