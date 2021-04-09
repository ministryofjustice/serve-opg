<?php

namespace App\Tests\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;

class ManageControllerTest extends AbstractControllerTestCase
{
    public function testPingdom()
    {
        $this->client->request('GET', '/manage/availability/pingdom');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testElb()
    {
        $this->client->request('GET', '/manage/elb');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
