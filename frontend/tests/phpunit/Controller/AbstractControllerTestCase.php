<?php

namespace AppBundle\Controller;

use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    public function setUp()
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => true]);

    }
}
