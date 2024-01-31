<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\NotifyClientMock;
use App\Tests\ApiWebTestCase;
use App\TestHelpers\UserTestHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserControllerTest extends ApiWebTestCase
{

    /**
     * @return Client
     */
    public function createAdminUserAndAuthenticate()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword
            ]
        );

        return $client;
    }

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
        $user = $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk', $this->behatPassword));
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));

        $userClient = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'user@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword
            ]
        );
        $userClient->catchExceptions(false);

        $adminClient = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword
            ]
        );

        $urlReplaced = str_replace('{id}', $user->getId(), $url);

        $this->expectException(AccessDeniedException::class);
        $userClient->request(Request::METHOD_GET, $urlReplaced, [], [], ['HTTP_REFERER' => '/users']);
        self::assertEquals(Response::HTTP_FORBIDDEN, $userClient->getResponse()->getStatusCode());

        $adminClient->request(Request::METHOD_GET, $urlReplaced, [], [], ['HTTP_REFERER' => '/users']);
        self::assertNotEquals(Response::HTTP_FORBIDDEN, $adminClient->getResponse()->getStatusCode());
    }

    public function testListUsers()
    {
        $user = UserTestHelper::createAdminUser('testUser@digital.justice.gov.uk', $this->behatPassword);

        $loginDate = new DateTime('2019-07-01');
        $loginDate->setTime(01, 01, 01 );
        $user->setLastLoginAt($loginDate);
        $this->persistEntity($user);

        $client = $this->createAdminUserAndAuthenticate();

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

        $client = ApiWebTestCase::getService('test.client');
        $crawler = $client->request(Request::METHOD_GET, "/login");
        $form = $crawler->selectButton('Sign in')->form(
            ['email' => 'test@digital.justice.gov.uk', 'password' => 'password']
        );

        $client->submit($form);

        $updatedUser = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('test@digital.justice.gov.uk');

        self::assertInstanceOf(DateTime::class, $updatedUser->getLastLoginAt());

        $today = (new DateTime('today'))->format('Y-m-d');
        self::assertEquals($today, $updatedUser->getLastLoginAt()->format('Y-m-d'));
    }


    public function testViewUsersAccessibleByAdminUsersOnly()
    {
        $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));
        $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk', $this->behatPassword));

        $tests = [
            ['creds' => ['PHP_AUTH_USER' => 'admin@digital.justice.gov.uk', 'PHP_AUTH_PW' => $this->behatPassword], 'expectedResponse' => Response::HTTP_OK],
            ['creds' => ['PHP_AUTH_USER' => 'user@digital.justice.gov.uk', 'PHP_AUTH_PW' => $this->behatPassword], 'expectedResponse' => Response::HTTP_FORBIDDEN],
        ];

        foreach ($tests as $test) {
            $client = ApiWebTestCase::getService('test.client');
            $client->setServerParameters($test['creds']);

            if (Response::HTTP_FORBIDDEN == $test['expectedResponse']) {
                $client->catchExceptions(false);
                $this->expectException(AccessDeniedException::class);
            }

            $client->request(Request::METHOD_GET, "/users");
            $this->assertEquals($test['expectedResponse'], $client->getResponse()->getStatusCode());
        }
    }

    public function testNewUserCreated()
    {
        $client = $this->createAdminUserAndAuthenticate();

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
        $client = $this->createAdminUserAndAuthenticate();

        $crawler = $client->request('GET', '/users/add');

        self::assertEquals('required', $crawler->filter('#user_form_email', false)->attr('required'));
        self::assertEquals('required', $crawler->filter('#user_form_firstName', false)->attr('required'));
        self::assertEquals('required', $crawler->filter('#user_form_lastName', false)->attr('required'));
        self::assertNull($crawler->filter('#user_form_phoneNumber')->attr('required'));

        $submittedCrawler = $client->submitForm('Add user', []);

        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-email')->attr('class'));
        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-firstName')->attr('class'));
        self::assertStringContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-lastName')->attr('class'));
        self::assertStringNotContainsString('govuk-form-group--error', $submittedCrawler->filter('#form-group-phoneNumber')->attr('class'));

        self::assertStringContainsString('Enter an email address', $submittedCrawler->filter('#form-group-email')->text(null, false));
    }

    public function testNewUserRequiresEmailFormat()
    {
        $client = $this->createAdminUserAndAuthenticate();

        $client->request('GET', '/users/add');
        $crawler = $client->submitForm('Add user', [
            'user_form[email]' => 'notanemail'
        ]);

        self::assertStringContainsString('govuk-form-group--error', $crawler->filter('#form-group-email')->attr('class'));
        self::assertStringContainsString('The email "notanemail" is not a valid email', $crawler->filter('#form-group-email')->text(null, false));
    }

    public function testCannotCreateUserWithMissingFields()
    {
        $client = $this->createAdminUserAndAuthenticate();

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
        $client = $this->createAdminUserAndAuthenticate();

        $client->request('GET', '/users/add');
        $crawler = $client->submitForm('Add user', [
            'user_form[email]' => 'admin@digital.justice.gov.uk',
            'user_form[firstName]' => 'Karol',
            'user_form[lastName]' => 'Gowey'
        ]);

        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringContainsString('govuk-form-group--error', $emailFormGroup->attr('class'));
        self::assertStringContainsString('Email address already in use', $emailFormGroup->text(null, false));
    }

    public function testActivationEmailSentToNewUser()
    {
        $client = $this->createAdminUserAndAuthenticate();

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
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

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
        $this->persistEntity(UserTestHelper::createUser('existing-email@digital.justice.gov.uk', $this->behatPassword));
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $client->request('GET', "/users/$userId/edit");
        $crawler = $client->submitForm('Update user', [
            'user_form[email]' => 'existing-email@digital.justice.gov.uk'
        ]);

        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringContainsString('govuk-form-group--error', $emailFormGroup->attr('class'));
        self::assertStringContainsString('Email address already in use', $emailFormGroup->text(null, false));
    }

    public function testUserEditDoesntWarnActivationEmail()
    {
        $user = $this->persistEntity(UserTestHelper::createUser('test@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $crawler = $client->request('GET', "/users/$userId/edit");
        $emailFormGroup = $crawler->filter('#form-group-email');

        self::assertStringNotContainsString('An activation email will be sent to the provided email address', $emailFormGroup->text(null, false));
    }

    public function testDeleteUser()
    {
        $user = $this->persistEntity(UserTestHelper::createUser('user@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $client->request(Request::METHOD_GET, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNull($this->getEntityManager()->getRepository(User::class)->find($userId));
        self::assertEquals('User successfully deleted', $this->getService('session')->getFlashBag()->get('success')[0]);

    }

    public function testUserCannotDeleteThemselves()
    {
        $adminUser = $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));
        $userId = $adminUser->getId();

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword
            ]
        );

        $client->request(Request::METHOD_GET, "/users/${userId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->getEntityManager()->getRepository(User::class)->find($userId));
        self::assertEquals('A user cannot delete their own account', $this->getService('session')->getFlashBag()->get('error')[0]);

    }

    public function testUnknownUserIsHandled()
    {
        $adminUser = $this->persistEntity(UserTestHelper::createAdminUser('admin@digital.justice.gov.uk', $this->behatPassword));
        $wrongUserId = $adminUser->getId() + 10;

        $client = $this->createAuthenticatedClient(
            [
                'PHP_AUTH_USER' => 'admin@digital.justice.gov.uk',
                'PHP_AUTH_PW' => $this->behatPassword
            ]
        );

        $client->request(Request::METHOD_GET, "/users/${wrongUserId}/delete", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertEquals('The user does not exist', $this->getService('session')->getFlashBag()->get('error')[0]);
    }

    public function testResendsActivationEmail()
    {
        $user = $this->persistEntity(UserTestHelper::createUser('test-activation@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $client->request(Request::METHOD_GET, "/users/$userId/resend-activation");

        self::assertEquals($user->getEmail(), NotifyClientMock::getLastEmail()['to']);
    }

    public function testUserInformedIfEmailFails()
    {
        $user = $this->persistEntity(UserTestHelper::createUser('test-activation@digital.justice.gov.uk', $this->behatPassword));
        $userId = $user->getId();

        $client = $this->createAdminUserAndAuthenticate();

        NotifyClientMock::$failNext = true;
        $client->request(Request::METHOD_GET, "/users/$userId/resend-activation");

        self::assertCount(1, $this->getService('session')->getFlashBag()->peekAll());
        self::assertEquals('Activation email could not be sent', $this->getService('session')->getFlashBag()->get('error')[0]);
    }

    public function passwordResetData()
    {
        return [
            [null, 'Set your password', 'To set your password,'],
            [new DateTime(), 'Reset your password', 'To reset your password,'],
        ];
    }

    /**
     * @dataProvider passwordResetData
     */
    public function testPasswordResetChangesContent($lastLoginAt, $expectedTitle, $expectedContent)
    {
        $token = uniqid();

        $user = UserTestHelper::createUser('test-reset@digital.justice.gov.uk', $this->behatPassword);
        $user->setActivationToken($token);
        $user->setLastLoginAt($lastLoginAt);
        $this->persistEntity($user);

        $client = ApiWebTestCase::getService('test.client');

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/user/password-reset/change/$token");

        self::assertStringContainsString($expectedTitle, $crawler->filter('title')->text(null, false ));
        self::assertStringContainsString($expectedContent, $crawler->filter('body')->text(null, false));
    }

    public function testAddConfirmationContainsEmail()
    {
        $addedUser = $this->persistEntity(UserTestHelper::createUser('addedUser@digital.justice.gov.uk', $this->behatPassword));

        $addedUserId = $addedUser->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $client->request(Request::METHOD_GET, "/users/${addedUserId}/confirmation", [], [], ['HTTP_REFERER' => '/users']);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('addedUser@digital.justice.gov.uk', $client->getResponse()->getContent());
    }

    public function testAddConfirmationLinksToDetails()
    {
        $addedUser = $this->persistEntity(UserTestHelper::createUser('addedUser@digital.justice.gov.uk', $this->behatPassword));

        $addedUserId = $addedUser->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $crawler = $client->request(Request::METHOD_GET, "/users/${addedUserId}/confirmation", [], [], ['HTTP_REFERER' => '/users']);

        $activationTextLink = $crawler->selectLink('resend the activation email')->link()->getUri();
        self::assertStringContainsString("/users/${addedUserId}/view", $activationTextLink);
    }

    public function testUserDetailsCorrect()
    {
        $addedUser = UserTestHelper::createUser('addedUser@digital.justice.gov.uk', $this->behatPassword);
        $addedUser->setFirstName('Added');
        $addedUser->setLastName('User');
        $addedUser->setPhoneNumber('01211234567');

        $this->persistEntity($addedUser);

        $addedUserId = $addedUser->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $crawler = $client->request(Request::METHOD_GET, "/users/${addedUserId}/view", [], [], ['HTTP_REFERER' => '/users']);

        self::assertStringContainsString("Added User", $crawler->html());
        self::assertStringContainsString("01211234567", $crawler->html());
        self::assertStringContainsString("Case manager", $crawler->html());
    }

    public function testUserDetailsLinkToEdit()
    {
        $addedUser = $this->persistEntity(UserTestHelper::createUser('addedUser@digital.justice.gov.uk', $this->behatPassword));
        $addedUserId = $addedUser->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $crawler = $client->request(Request::METHOD_GET, "/users/${addedUserId}/view", [], [], ['HTTP_REFERER' => '/users']);

        $activationTextLink = $crawler->selectLink('Edit details')->link()->getUri();
        self::assertStringContainsString("/users/${addedUserId}/edit", $activationTextLink);
    }

    public function testUserDetailsShowActivationReminder()
    {
        /** @var User $addedUser */
        $addedUser = $this->persistEntity(UserTestHelper::createUser('addedUser@digital.justice.gov.uk', $this->behatPassword));
        $addedUser->setLastLoginAt(null);
        $addedUser->setActivationToken('Abc123');

        $addedUserId = $addedUser->getId();

        $client = $this->createAdminUserAndAuthenticate();

        $client->request(Request::METHOD_GET, "/users/${addedUserId}/view", [], [], ['HTTP_REFERER' => '/users']);

        $this->assertMatchesRegularExpression(
            '^This user has not activated their account. To resend an activation email click <a href="\/users\/[0-9]*\/resend-activation">here<\/a>\..*^',
            $this->getService('session')->getFlashBag()->get('warn')[0]
        );
    }
}
