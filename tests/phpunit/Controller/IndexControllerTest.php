<?php

namespace tests\phpunit\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;
//use GuzzleHttp\Message\Response;
//use GuzzleHttp\Message\ResponseInterface;

class IndexControllerTest extends AbstractControllerTestCase
{
    public function testHomePage()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
