<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\Order;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\UserTestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiWebTestCase
{
    public function testListUsers()
    {
        $user = UserTestHelper::createUser('test@digital.justice.gov.uk');
        self::persistEntity($user);

        /** @var Client $client */
        $client = $this->getService('test.client');

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/users/view", [], [], self::BASIC_AUTH_CREDS);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }
}
