<?php declare(strict_types=1);


namespace App\Tests\Functional;


use App\Entity\Order;
use App\Entity\User;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\ProcessManager\WebServerReadinessProbeTrait;

class UploadingCourtOrderTest extends PantherTestCase
{
    const TEST_USER_PASSWORD = 'password123';
    const TEST_USER_EMAIL = 'test@user.com';
    const BASIC_AUTH_CREDS = ['PHP_AUTH_USER' => self::TEST_USER_EMAIL, 'PHP_AUTH_PW' => self::TEST_USER_PASSWORD];

    public function setUp()
    {
        self::bootKernel();
        self::purgeDatabase();
        self::createTestUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    public function testUploadValidWordDoc()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', Order::TYPE_HW);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var Client $client */
        $client = static::createPantherClient(['external_base_uri' => 'https://loadbalancer']);
        $client->followRedirects();

        $client->request('GET', '/login', [], []);
        $client->submitForm('Sign in', ['_username' => self::TEST_USER_EMAIL, '_password' => self::TEST_USER_PASSWORD]);

        $crawler = $client->clickLink($caseNumber);
        self::assertContains("/order/${orderId}/upload", $client->getCurrentURL());

        /** @var RemoteWebElement $fileInput */
        $fileInput = $client->findElement(WebDriverBy::cssSelector('input[type="file"].dz-hidden-input'));
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys('../TestData/validCO - 93559316.docx');
        $client->waitFor('div.dz-filename', 5);

        $crawler->selectButton('Continue')->click();

        self::assertContains("/order/${orderId}/summary", $client->getCurrentURL());

        $orderDetails = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('.govuk-table__body'))->getText();

        self::assertContains('New application', $orderDetails);
        self::assertContains('Joint and several', $orderDetails);
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