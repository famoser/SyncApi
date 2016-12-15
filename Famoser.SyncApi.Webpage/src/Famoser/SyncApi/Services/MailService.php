<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 15.12.2016
 * Time: 10:41
 */

namespace Famoser\SyncApi\Services;


use Famoser\SyncApi\Services\Base\BaseService;
use Famoser\SyncApi\Services\Interfaces\MailServiceInterface;

/**
 * sends emails with PHPMailer or falls back to default mail() function
 * @package Famoser\SyncApi\Services
 */
class MailService extends BaseService implements MailServiceInterface
{

    /**
     * sends an email to the specified recipient(s)
     * @param string $sender
     * @param string|array $receiver
     * @param string $subject
     * @param string $message
     * @return bool
     */
    public function sendMail($sender, $receiver, $subject, $message)
    {
        if (isset($this->getSettingsArray()["mail"])) {

        } else {
            $headers = "From: " . $sender;
            return mail($receiver, $subject, $message, $headers, "-f " . $sender);
        }
    }
}