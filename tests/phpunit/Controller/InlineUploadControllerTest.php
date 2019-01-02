<?php

namespace AppBundle\Controller;

class InlineUploadControllerTest extends AbstractControllerTestCase
{
    public function testRequest()
    {
        $this->client->request('GET', '/upload-document');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
