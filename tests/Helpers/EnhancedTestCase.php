<?php declare(strict_types=1);


namespace App\Tests\Helpers;


use App\Entity\OrderHw;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EnhancedTestCase extends WebTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW'   => self::TEST_USER_PASSWORD];
    /**
     * @var array
     */
    protected $basicAuthCreds;

    /**
     * @var string
     */
    protected $testUserPassword;

    /**
     * @var string
     */
    protected $testUserEmail;

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
        return $this->getService('doctrine.orm.entity_manager');
    }

    protected function persistEntity(object $entity)
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
}