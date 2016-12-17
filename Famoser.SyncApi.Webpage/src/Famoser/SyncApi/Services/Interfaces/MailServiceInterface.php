<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.12.2016
 * Time: 10:38
 */

namespace Famoser\SyncApi\Services\Interfaces;


/**
 * sends emails
 *
 * @package Famoser\SyncApi\Services\Interfaces
 */
interface MailServiceInterface
{
    /**
     * sends an email to the specified recipient(s)
     * @param string $sender
     * @param string|array $receiver
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function sendMail($sender, $receiver, $subject, $body);
}