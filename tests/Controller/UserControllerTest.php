<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\NotifyClientMock;
use App\Tests\ApiWebTestCase;
use App\TestHelpers\UserTestHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiWebTestCase
{
    public function adminURLProvider()
    {
        return [
            ['/users'],
            ['/users/add'],
            ['/users/{id}/edit'],
            ['/users/{id}/view'],
            ['/users/{id}/delete'],
            ['/users/{id}/resend-activation'],
        ];
    }

    /**
     * @dataProvider adminURLProvider
     */
    public function testAdminURLsRequireRole($url)
    {
        $user = $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk'));
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $userClient = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'user@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $adminClient = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => 'Abcd1234'
            ]
        );

        $urlReplaced = str_replace('{id}', $user->getId(), $url);

        $userClient->request(Request::METHOD_GET, $urlReplaced, [], [], ['HTTP_REFERER' => '/users']);
        self::assertEquals(Response::HTTP_FORBIDDEN, $userClient->getResponse()->getStatusCode());

        $adminClient->request(Request::METHOD_GET, $urlReplaced, [], [], ['HTTP_REFERER' => '/users']);
        self::assertNotEquals(Response::HTTP_FORBIDDEN, $adminClient->getResponse()->getStatusCode());
    }

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

        $crawler = $client->request(Request::METHOD_GET, "/users");

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('testUser@digital.justice.gov.uk', $client->getResponse()->getContent());

        $linkUrl = $crawler->selectLink('Add new user')->link()->getUri();
        self::assertStringContainsString('/users/add', $linkUrl);
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

            $client->request(Request::METHOD_GET, "/users");
            $this->assertEquals($test['expectedResponse'], $client->getResponse()->getStatusCode());
        }
    }

/**
 * /add-confirmed
 *  - testAddConfirmationContainsEmail
 *  - testAddConfirmationLinksToDetails
 * /view
 *  - testUserDetailsCorrect
 *  - testUserDetailsLinkToEdit
 *  - testUserDetailsShowActivationReminder
 */

    public function testNewUserCreated()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request('GET', '/users/add');
        $client->submitForm('Add user', [
            'user_form[email]' => 'b.vorpahl@digital.justice.gov.uk',
            'user_form[firstName]' => 'Bennie',
            'user_form[lastName]' => 'Vorpahl',
            'user_form[roleName]' => 'ROLE_ADMIN'
        ]);

        $newUser = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('b.vorpahl@digital.justice.gov.uk');

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertEquals('Bennie', $newUser->getFirstName());
        self::assertEquals('Vorpahl', $newUser->getLastName());
        self::assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $newUser->getRoles());
    }

    public function testNewUserFieldsRequired()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $crawler = $client->request('GET', '/users/add');

        self::assertEquals('required', $crawler->filter('#user_form_email')->attr('required'));
        self::assertEquals('required', $crawler->filter('#user_form_firstName')->attr('required'));
        self::assertEquals('required', $crawler->filter('#user_form_lastName')->attr('required'));
        self::assertNull($crawler->filter('#user_form_phoneNumber')->attr('required'));

        $submittedCrawler = $client->submitForm('Add user', []);

        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-email')->attr('class'));
        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-firstName')->attr('class'));
        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-lastName')->attr('class'));
        self::assertStringNotContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-phoneNumber')->attr('class'));

        self::assertStringContainsString('Enter an email address', $submittedCrawler->filter('#form-group-email')->text());
    }

    public function testNewUserRequiresEmailFormat()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request('GET', '/users/add');
        $crawler = $client->submitForm('Add user', [
            'user_form[email]' => 'notanemail'
        ]);

        self::assertStringContainsString('govuk-form-group--error', $crawler->filter('#form-group-email')->attr('class'));
        self::assertStringContainsString('The email "notanemail" is not a valid email', $crawler->filter('#form-group-email')->text());
    }

    public function testCannotCreateUserWithMissingFields()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $email = 'michele.gallington@digital.justice.gov.uk';

        $client->request('GET', '/users/add');
        $client->submitForm('Add user', [
            'user_form[email]' => $email
        ]);

        $newUser = $this->getEntityManager()->getRepository(User::class)->findOneByEmail($email);

        self::assertNull($newUser);
    }

    public function testCannotCreateUserWithExistingEmail()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request('GET', '/users/add');
        $crawler = $client->submitForm('Add user', [
            'user_form[email]' => 'admin@digital.justice.gov.uk',
            'user_form[firstName]' => 'Karol',
            'user_form[lastName]' => 'Gowey'
        ]);

        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringContainsString('govuk-form-group--error', $emailFormGroup->attr('class'));
        self::assertStringContainsString('Email address already in use', $emailFormGroup->text());
    }

    public function testActivationEmailSentToNewUser()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $email = 'velia.santalucia@digital.justice.gov.uk';

        $client->request('GET', '/users/add');
        $client->submitForm('Add user', [
            'user_form[email]' => $email,
            'user_form[firstName]' => 'Velia',
            'user_form[lastName]' => 'Santalucia',
            'user_form[roleName]' => 'ROLE_USER'
        ]);

        self::assertEquals($email, NotifyClientMock::getLastEmail()['to']);
    }

    public function testEditUser()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request('GET', "/users/$userId/edit");
        $client->submitForm('Update user', [
            'user_form[email]' => 'scot.woehrle@digital.justice.gov.uk',
            'user_form[firstName]' => 'Scot',
            'user_form[lastName]' => 'Woehrle',
            'user_form[roleName]' => 'ROLE_ADMIN'
        ]);

        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

        self::assertEquals('scot.woehrle@digital.justice.gov.uk', $user->getEmail());
        self::assertEquals('Scot', $user->getFirstName());
        self::assertEquals('Woehrle', $user->getLastName());
        self::assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
    }

    public function testCannotEditUserToExistingEmail()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $this->persistEntity(UserTestHelper::createUser('existing-email@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request('GET', "/users/$userId/edit");
        $crawler = $client->submitForm('Update user', [
            'user_form[email]' => 'existing-email@digital.justice.gov.uk'
        ]);

        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringContainsString('govuk-form-group--error', $emailFormGroup->attr('class'));
        self::assertStringContainsString('Email address already in use', $emailFormGroup->text());
    }

    public function testUserEditDoesntWarnActivationEmail()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $crawler = $client->request('GET', "/users/$userId/edit");
        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringNotContainsString('An activation email will be sent to the provided email address', $emailFormGroup->text());
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

        $client->request(Request::METHOD_GET, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNull($this->getEntityManager()->getRepository(User::class)->find($userId));
        self::assertEquals('User successfully deleted', $this->getService('session')->getFlashBag()->get('success')[0]);

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

        $client->request(Request::METHOD_GET, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->getEntityManager()->getRepository(User::class)->find($userId));
        self::assertEquals('A user cannot delete their own account', $this->getService('session')->getFlashBag()->get('error')[0]);

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

        $client->request(Request::METHOD_GET, "/users/${wrongUserId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertEquals('The user does not exist', $this->getService('session')->getFlashBag()->get('error')[0]);
    }

    public function testResendsActivationEmail()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('test-activation@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        $client->request(Request::METHOD_GET, "/users/$userId/resend-activation");

        self::assertEquals($user->getEmail(), NotifyClientMock::getLastEmail()['to']);
    }

    public function testUserInformedIfEmailFails()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk'));
        $user = $this->persistEntity(UserTestHelper::createUser('test-activation@digital.justice.gov.uk'));
        $userId = $user->getId();

        $client = $this->createAuthenticatedClient(
            [ 'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234' ]
        );

        NotifyClientMock::$failNext = true;
        $client->request(Request::METHOD_GET, "/users/$userId/resend-activation");

        self::assertEquals('Activation email could not be sent', $this->getService('session')->getFlashBag()->get('error')[0]);
    }
}
