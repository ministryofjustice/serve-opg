<?php

namespace App\Tests\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;
use Symfony\Component\Console\Logger\ConsoleLogger;

class HealthControllerTest extends AbstractControllerTestCase
{

    public function testServiceHealth()
    {
        $this->client->request('GET', '/health-check/service');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }


    public function testContainerHealth()
    {
        $this->client->request('GET', '/health-check');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
