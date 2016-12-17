<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.12.2016
 * Time: 16:06
 */

namespace Famoser\SyncApi\Tests\TestHelpers\MockServices;


use Famoser\SyncApi\Services\Interfaces\MailServiceInterface;
use PHPUnit_Framework_TestCase;

/**
 * checks how its methods are called
 * @package Famoser\SyncApi\Tests\TestHelpers\MockServices
 */
class MockMailService implements MailServiceInterface
{
    private $cache;

    /**
     * sends an email to the specified recipient(s)
     * @param string $sender
     * @param string|array $receiver
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function sendMail($sender, $receiver, $subject, $body)
    {
        $this->cache = [];
        $this->cache["sender"] = $sender;
        $this->cache["receiver"] = $receiver;
        $this->cache["subject"] = $subject;
        $this->cache["message"] = $body;
        return true;
    }

    /**
     * @return array
     */
    public function getCache()
    {
        return $this->cache;
    }
}