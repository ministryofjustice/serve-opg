<?php declare(strict_types=1);


namespace App\Tests;

use App\Entity\OrderHw;
use App\Service\TimeService;
use App\TestHelpers\OrderTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

class BaseFunctionalTestCase extends PantherTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW' => self::TEST_USER_PASSWORD];
    private $projectDir;

    public static function setUpBeforeClass(): void
    {
        ClockMock::register(TimeService::class);
    }

    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->projectDir = $this->getService('kernel')->getProjectDir();
    }

    /**
     * @todo drop once behat tests are stateless
     */
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
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    /**
     * @param string $madeAt
     * @param string $issuedAt
     * @param string $caseNumber
     * @param string $orderType
     * @return \App\Entity\OrderHw
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createAndPersistOrder(string $madeAt, string $issuedAt, string $caseNumber, string $orderType)
    {
        $order = OrderTestHelper::generateOrder($madeAt, $issuedAt, $caseNumber, $orderType);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();
        return $order;
    }

    protected function createAuthenticatedSymfonyClient($creds=self::BASIC_AUTH_CREDS)
    {
        $client = static::createClient();
        $client->setServerParameters($creds);
        return $client;
    }

    protected function createAuthenticatedPantherClient()
    {
        /** @var PantherClient $client */
        $client = static::createPantherClient(['external_base_uri' => 'https://loadbalancer']);
        $client->followRedirects();
        // @todo find a way to hook into WebDriver cookie and set auth using session
        // https://symfony.com/doc/current/testing/http_authentication.html https://twitter.com/dunglas/status/1039539719208660992?s=20
        $client->request('GET', '/login', [], []);
        $client->submitForm('Sign in', ['_username' => self::TEST_USER_EMAIL, '_password' => self::TEST_USER_PASSWORD]);
        return $client;
    }

    /**
     * @param PantherClient $client
     * @param string $localFileLocation, the full file path - e.g. '/tests/TestData/filename.png'
     */
    protected function uploadDropzoneFile(PantherClient $client, string $localFileLocation)
    {
        /** @var RemoteWebElement $fileInput */
        $fileInput = $client->findElement(WebDriverBy::cssSelector('input[type="file"].dz-hidden-input'));
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys($this->projectDir . $localFileLocation);
    }

    protected function createTestUser(string $email, string $password)
    {
        $user = UserTestHelper::createUser($email, $password);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $orderType
     * @return OrderHw
     * @throws \Exception
     */
    protected function createOrder(string $orderType)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);
        return $this->persistEntity($order);
    }

    protected function persistEntity(object $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    protected function timeTravel(string $dateTime)
    {
        ClockMock::withClockMock(strtotime($dateTime));
    }
}
