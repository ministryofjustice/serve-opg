<?php

namespace App\Phpunit\Helpers;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    public function setUp(): void
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);
    }

}
