<?php

namespace App\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;
//use GuzzleHttp\Message\Response;
//use GuzzleHttp\Message\ResponseInterface;

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
