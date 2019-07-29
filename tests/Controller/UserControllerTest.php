<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\UserTestHelper;
use DateTime;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiWebTestCase
{
    public function testListUsers()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $user = UserTestHelper::createAdminUser('testUser@digital.justice.gov.uk');

        $loginDate = new DateTime('2019-07-01');
        $loginDate->setTime(01, 01, 01 );
        $user->setLastLoginAt($loginDate);
        $this->persistEntity($user);

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $client->request(Request::METHOD_GET, "/users/view");

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('testUser@digital.justice.gov.uk', $client->getResponse()->getContent());
        self::assertStringContainsString('2019-07-01 01:01:01', $client->getResponse()->getContent());

        $today = (new DateTime('today'))->format('Y-m-d');
        self::assertStringContainsString($today, $client->getResponse()->getContent());
    }

    public function testUserLoginUpdatesLastLoginAt()
    {
        $user = $this->persistEntity(UserTestHelper::createAdminUser('test@digital.justice.gov.uk', 'password'));

        self::assertNull($user->getLastLoginAt());

        $client = ApiWebTestCase::getService('test.client');;
        $crawler = $client->request(Request::METHOD_GET, "/login");
        $form = $crawler->selectButton('Sign in')->form(
            ['_username' => 'test@digital.justice.gov.uk', '_password' => 'password']
        );

        $client->submit($form);

        $updatedUser = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('test@digital.justice.gov.uk');

        self::assertInstanceOf(DateTime::class, $updatedUser->getLastLoginAt());

        $today = (new DateTime('today'))->format('Y-m-d');
        self::assertEquals($today, $updatedUser->getLastLoginAt()->format('Y-m-d'));
    }


    public function testViewUsersAccessibleByAdminUsersOnly()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk'));

        $tests = [
            ['creds' => ['PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234'], 'expectedResponse' => Response::HTTP_OK],
            ['creds' => ['PHP_AUTH_USER' => 'user@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234'], 'expectedResponse' => Response::HTTP_FORBIDDEN],
        ];

        foreach ($tests as $test) {
            $client = ApiWebTestCase::getService('test.client');
            $client->setServerParameters($test['creds']);

            $client->request(Request::METHOD_GET, "/users/view");
            $this->assertEquals($test['expectedResponse'], $client->getResponse()->getStatusCode());
        }
    }

    public function testDeleteUser()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $client->request(Request::METHOD_DELETE, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users/view']);
        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNull($this->getEntityManager()->getRepository(User::class)->find($userId));
    }

    public function testUserCannotDeleteThemselves()
    {
        $adminUser = $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $userId = $adminUser->getId();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $client->request(Request::METHOD_DELETE, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users/view']);
        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->getEntityManager()->getRepository(User::class)->find($userId));
    }

    public function testUnknownUserIsHandled()
    {
        $adminUser = $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $wrongUserId = $adminUser->getId() + 10;

        /** @var Client $client */
        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $client->request(Request::METHOD_DELETE, "/users/${wrongUserId}/delete", [], [], ['HTTP_REFERER' => '/users/view']);
        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }
}
