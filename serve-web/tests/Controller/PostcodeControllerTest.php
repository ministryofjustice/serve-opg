<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\ApiWebTestCase;

class PostcodeControllerTest extends ApiWebTestCase
{
    // this test doesn't actually send a request to Ordnance Survey's API, as that could cause problems for us...
    public function testRequest()
    {
        $this->persistEntity($this->getUserTestHelper()->createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword,
            ]
        );

        $client->request('GET', '/postcode-lookup');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
