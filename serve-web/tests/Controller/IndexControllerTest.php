<?php

namespace App\Tests\Controller;

use App\Phpunit\Helpers\AbstractControllerTestCase;

class IndexControllerTest extends AbstractControllerTestCase
{
    public function testHomePage()
    {
        $this->client->request('GET', '/');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
