<?php declare(strict_types=1);


namespace App\Tests\Functional;


use App\Entity\Order;
use App\Tests\BaseFunctionalTestCase;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client as PantherClient;

class UploadingCourtOrderTest extends BaseFunctionalTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testUploadValidWordDoc()
    {
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_HW);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedPantherClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertStringContainsString("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '/tests/TestData/validCO_99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        self::assertStringContainsString("/order/${orderId}/summary", $client->getCurrentURL());

        $orderDetails = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('.govuk-table__body'))->getText();

        self::assertStringContainsString('New application', $orderDetails);
        self::assertStringContainsString('Joint and several', $orderDetails);
    }

    public function testUploadMissingAppointmentAndSubTypeDoc()
    {
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_HW);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedPantherClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertStringContainsString("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '/tests/TestData/Missing_appointment_and_sub_type_99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        $confirmOrderDetailsForm = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('form[name="confirm_order_details_form"]'));

        self::assertStringContainsString("/order/${orderId}/confirm-order-details", $client->getCurrentURL());
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_subType'))->getText());
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_appointmentType'))->getText());
    }

    public function testUploadMissingBondAmountDoc()
    {
        $order = self::createAndPersistOrder('2018-08-01', '2018-08-10', '99900002', Order::TYPE_PF);
        $orderId = $order->getId();
        $caseNumber = $order->getClient()->getCaseNumber();

        /** @var PantherClient $client */
        $client = self::createAuthenticatedPantherClient();

        $client->request('GET', '/case', [], []);
        $crawler = $client->clickLink($caseNumber);
        self::assertStringContainsString("/order/${orderId}/upload", $client->getCurrentURL());

        self::uploadDropzoneFile($client, '/tests/TestData/Missing_bond_amount_99900002.docx');
        $client->waitFor('a.dropzone__file-remove', 5);

        $crawler->selectButton('Continue')->click();

        self::assertStringContainsString("/order/${orderId}/confirm-order-details", $client->getCurrentURL());

        $confirmOrderDetailsForm = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('form[name="confirm_order_details_form"]'));
        self::assertNotNull($confirmOrderDetailsForm->findElement(WebDriverBy::id('confirm_order_details_form_hasAssetsAboveThreshold'))->getText());
    }
}
