<?php declare(strict_types=1);


namespace App\Tests;

use App\Entity\OrderHw;
use App\TestHelpers\OrderTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiWebTestCase extends WebTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW' => self::TEST_USER_PASSWORD];
    protected $behatPassword;

    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->behatPassword = static::getContainer()->get('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface')->get('behat_password');
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger(ApiWebTestCase::getService('doctrine')->getManager());
        $purger->purge();
    }

    protected static function getService($id)
    {
        return static::getContainer()->get($id);
    }

    /**
     * @return OrderHw
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createOrder(string $orderType)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);
        return $this->persistEntity($order);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return ApiWebTestCase::getService('doctrine.orm.entity_manager');
    }

    protected function persistEntity(object $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    protected function createTestUser(string $email, string $password)
    {
        $user = UserTestHelper::createUser($email, $password);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param array $creds
     * @return Client
     */
    protected function createAuthenticatedClient($creds=self::BASIC_AUTH_CREDS)
    {
        $client = ApiWebTestCase::getService('test.client');
        $client->setServerParameters($creds);
        return $client;
    }
}
