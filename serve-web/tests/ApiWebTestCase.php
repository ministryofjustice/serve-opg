<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\TestHelpers\OrderTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiWebTestCase extends WebTestCase
{
    public const string TEST_USER_PASSWORD = 'password123';
    public const string TEST_USER_EMAIL = 'test@user.com';
    public const array BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW' => self::TEST_USER_PASSWORD];
    protected string $behatPassword;
    private ?UserTestHelper $userTestHelper = null;

    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);

        $this->behatPassword = $this->getContainer()->get('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface')->get('behat_password');
    }

    protected function getUserTestHelper(): UserTestHelper
    {
        if (!$this->userTestHelper instanceof UserTestHelper) {
            $this->userTestHelper = new UserTestHelper();
        }

        return $this->userTestHelper;
    }

    protected function purgeDatabase(): void
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id): ?object
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @throws OptimisticLockException|\Exception|ORMException
     */
    protected function createOrder(string $orderType): object
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);

        return $this->persistEntity($order);
    }

    protected function getEntityManager(): EntityManager
    {
        /** @var EntityManager $em */
        $em = $this->getService('doctrine.orm.entity_manager');

        return $em;
    }

    protected function persistEntity(object $entity): object
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function createTestUser(string $email, string $password): User
    {
        $user = $this->getUserTestHelper()->createUser($email, $password);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    protected function getAuthenticatedClient(array $creds = self::BASIC_AUTH_CREDS): KernelBrowser
    {
        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');

        $client->setServerParameters($creds);

        return $client;
    }
}
