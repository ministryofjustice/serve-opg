<?php

namespace AppBundle\Controller;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use Mockery as m;

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
