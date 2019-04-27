<?php

namespace App\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;

class PostcodeControllerTest extends AbstractControllerTestCase
{
    public function testRequest()
    {
        $this->client->request('GET', '/postcode-lookup');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
