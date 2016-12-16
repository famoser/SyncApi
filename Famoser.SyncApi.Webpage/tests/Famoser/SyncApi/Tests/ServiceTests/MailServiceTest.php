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

        if ($mailService->sendMail(
            ["git@famoser.ch" => "Florian Moser"],
            ["blasero@wont-tell-email" => "Blasero"],
            "hallo!",
            "Ich bin gerade am testen!"
        )
        ) {
            //test only if server is allowed to send emails
            $files = glob($folderPath);
            static::assertTrue(count($files) == 1);
            $file = file_get_contents($files[0]);
            static::assertContains("hallo", $file);
            static::assertContains("git@famoser.ch", $file);
            static::assertContains("Florian Moser", $file);
            static::assertContains("Ich bin gerade am testen!", $file);
            static::assertContains("Blasero", $file);
            static::assertContains("blasero@wont-tell-email", $file);
        }
    }
}