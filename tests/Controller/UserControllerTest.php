<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\Order;
use App\Entity\User;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\UserTestHelper;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiWebTestCase
{
    public function testListUsers()
    {
        /** @var User $user */
        $user = UserTestHelper::createUser('test@digital.justice.gov.uk');

        $loginDate = new DateTime('2019-07-01');
        $loginDate->setTime(01, 01, 01 );
        $user->setLastLoginAt($loginDate);

        $this->persistEntity($user);

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/users/view");

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertContains('test@digital.justice.gov.uk', $client->getResponse()->getContent());
        self::assertContains('2019-07-01 01:01:01', $client->getResponse()->getContent());
    }
}
