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
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

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
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_HW);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertContains("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '../TestData/validCO - 99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        self::assertContains("/order/${orderId}/summary", $client->getCurrentURL());

        $orderDetails = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('.govuk-table__body'))->getText();

        self::assertContains('New application', $orderDetails);
        self::assertContains('Joint and several', $orderDetails);
    }

    public function testUploadMissingAppointmentAndSubTypeDoc()
    {
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_HW);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertContains("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '../TestData/Missing appointment and sub type - 99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        $confirmOrderDetailsForm = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('form[name="confirm_order_details_form"]'));

        self::assertContains("/order/${orderId}/confirm-order-details", $client->getCurrentURL());
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_subType'))->getText());
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_appointmentType'))->getText());
    }

    public function testUploadMissingBondAmountDoc()
    {
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_PF);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertContains("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '../TestData/Missing bond amount - 99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        self::assertContains("/order/${orderId}/confirm-order-details", $client->getCurrentURL());

        $confirmOrderDetailsForm = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('form[name="confirm_order_details_form"]'));
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_hasAssetsAboveThreshold'))->getText());
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

    protected function createAuthenticatedClient()
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

    protected function uploadDropzoneFile(PantherClient $client, string $localFileLocation)
    {
        /** @var RemoteWebElement $fileInput */
        $fileInput = $client->findElement(WebDriverBy::cssSelector('input[type="file"].dz-hidden-input'));
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys($localFileLocation);
    }
}