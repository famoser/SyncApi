<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 16.12.2016
 * Time: 12:26
 */

namespace Famoser\SyncApi\Tests\ServiceTests;


use Famoser\SyncApi\Tests\ServiceTests\Base\BaseTestService;

/**
 * tests the mail service
 * @package Famoser\SyncApi\Tests\ServiceTests
 */
class MailServiceTest extends BaseTestService
{
    public function testSendMail()
    {
        $folderPath = __DIR__ . DIRECTORY_SEPARATOR . "mailoutput" . DIRECTORY_SEPARATOR . "*.txt";
        $files = glob($folderPath);
        foreach ($files as $file) {
            unlink($file);
        }

        $mailService = $this->getMailService();

        //swiftmail itself is well tested, we only confirm it throws no error
        static::assertTrue($mailService->sendMail(
            ["git@famoser.ch" => "Florian Moser"],
            ["blasero@wont-tell-email" => "Blasero"],
            "hallo!",
            "Ich bin gerade am testen!"
        ));
    }
}