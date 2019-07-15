<?php declare(strict_types=1);


namespace App\Tests\Functional;


use App\Entity\Order;
use App\Entity\User;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\ProcessManager\WebServerReadinessProbeTrait;

class UploadingCourtOrderTest extends PantherTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW'   => self::TEST_USER_PASSWORD];

    public function setUp()
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    public function testUploadValidWordDoc()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345', Order::TYPE_HW);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var Client $client */
//        $client = static::createPantherClient(['external_base_uri' => 'http://localhost']);

        $options = [
            '--headless',
            '--window-size=1200,1100',
            '--no-sandbox',
            '--disable-gpu',
            '--ignore-certificate-errors', '--allow-insecure-localhost', '--disable-dev-shm-usage', '--allow-running-insecure-content'
        ];

        /** @var Client $client */
        $client = Client::createChromeClient(null, $options, [], 'https://localhost');

        $crawler = $client->request('GET', '/login', [], []);
        $client->wait(1);
        $client->takeScreenshot('alex.png');

//        $ele = $crawler->filter('#login_username');
//        $blah = $client->clickLink($caseNumber);

        self::assertContains("/order/${orderId}/upload", $crawler->getUri());

        $crawler->selectButton('Choose documents')->form();

        $crawler = $client->submitForm();
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function createTestUser(string $email, string $password)
    {
        $userModel = new User($email);
        $encodedPassword = $this->getService('security.user_password_encoder.generic')->encodePassword($userModel, $password);
        $userModel->setPassword($encodedPassword);
        $this->getEntityManager()->persist($userModel);
        $this->getEntityManager()->flush();
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
}