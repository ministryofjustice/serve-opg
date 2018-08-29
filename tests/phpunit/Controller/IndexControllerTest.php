<?php

namespace AppBundle\Controller;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use Mockery as m;

class IndexControllerTest extends AbstractControllerTestCase
{
    public function testHomePage()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
