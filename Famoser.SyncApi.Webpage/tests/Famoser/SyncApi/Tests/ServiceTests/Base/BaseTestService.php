<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 16.12.2016
 * Time: 12:26
 */

namespace Famoser\SyncApi\Tests\ServiceTests\Base;


use Famoser\SyncApi\Framework\ContainerBase;
use Famoser\SyncApi\Tests\TestHelpers\FrontendTestHelper;

/**
 * a base class used for testing the services
 * @package Famoser\SyncApi\Tests\ServiceTests\Base
 */
class BaseTestService extends \PHPUnit_Framework_TestCase
{
    /* @var FrontendTestHelper $testHelper */
    protected $testHelper;

    /**
     * @return ContainerBase
     */
    private function getContainerBase()
    {
        return new ContainerBase($this->testHelper->getTestApp()->getContainer());
    }

    /**
     * returns a ready to use database service
     *
     * @return \Famoser\SyncApi\Services\Interfaces\DatabaseServiceInterface
     */
    protected function getDatabaseService()
    {
        return $this->getContainerBase()->getDatabaseService();
    }

    /**
     * returns the mail service
     *
     * @return \Famoser\SyncApi\Services\Interfaces\MailServiceInterface
     */
    protected function getMailService()
    {
        return $this->getContainerBase()->getMailService();
    }

    public function setUp()
    {
        $this->testHelper = new FrontendTestHelper();

    }

    public function tearDown()
    {
        $this->testHelper->cleanEnvironment();
    }
}