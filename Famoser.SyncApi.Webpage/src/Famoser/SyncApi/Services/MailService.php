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
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

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
        if (isset($this->getSettingsArray()["smtp_mail"])) {
            $mailSettings = $this->getSettingsArray()["mail"];
            $transport = Swift_SmtpTransport::newInstance($mailSettings["url"], $mailSettings["port"])
                ->setUsername($mailSettings["username"])
                ->setPassword($mailSettings["password"]);
        } else {
            $transport = new \Swift_MailTransport();
        }

        $mailer = Swift_Mailer::newInstance($transport);

        // Create a message
        $message = Swift_Message::newInstance($subject)
            ->setFrom($sender)
            ->setTo($receiver)
            ->setBody($message);

        $result = $mailer->send($message);
        return $result == count($receiver);
    }
}