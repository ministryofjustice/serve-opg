<?php declare(strict_types=1);


namespace App\Tests;

use App\Entity\OrderHw;
use App\Entity\User;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiWebTestCase extends WebTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW'   => self::TEST_USER_PASSWORD];

    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    protected function tearDown(): void
    {
        $this->purgeDatabase();
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$container->get($id);
    }

    /**
     * @param string $orderType
     * @param string $caseNumber
     * @return OrderHw
     * @throws \Exception
     */
    protected function createAndPersistOrder(string $orderType, string $caseNumber='93559316')
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', $caseNumber, $orderType);
        return $this->persistEntity($order);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    protected function persistEntity(object $entity)
    {
        $this->getEntityManager()->persist($entity);
        return $entity;
    }

    protected function persistAndFlushEntity(object $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    protected function createTestUser(string $email, string $password)
    {
        $userModel = new User($email);
        $encodedPassword = $this->getService('security.user_password_encoder.generic')->encodePassword($userModel, $password);
        $userModel->setPassword($encodedPassword);
        $this->getEntityManager()->persist($userModel);
        $this->getEntityManager()->flush();
    }

    protected function createAuthenticatedClient()
    {
        $client = $this->getService('test.client');
        $client->setServerParameters(self::BASIC_AUTH_CREDS);
        return $client;
    }
}
