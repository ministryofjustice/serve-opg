<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\CaseController;
use App\Entity\OrderPf;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\OrderTestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

class CaseControllerTest extends ApiWebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCaseLinkForUnservedNotValidCasesLinksToUploadView()
    {
        $unservedNotValidOrder = $this->createOrder(OrderPf::TYPE_PF, '12345678');
        $orderId = $unservedNotValidOrder->getId();
        $caseNumber = $unservedNotValidOrder->getClient()->getCaseNumber();

        /** @var Client $client */
        $client = $this->getService('test.client');

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/case", [], [], self::BASIC_AUTH_CREDS);
        $caseLink = $crawler->selectLink($caseNumber)->link();
        self::assertContains("/order/${orderId}/upload", $caseLink->getUri());
    }

    public function testCaseLinkForUnservedValidCasesLinksToSummaryView()
    {
        $unservedValidOrder = $this->createOrder(OrderPf::TYPE_PF, '12345678');
        $unservedValidOrder->setSubType(OrderPf::SUBTYPE_NEW);
        $unservedValidOrder->setAppointmentType(OrderPf::APPOINTMENT_TYPE_JOINT);
        $unservedValidOrder->setHasAssetsAboveThreshold(OrderPf::HAS_ASSETS_ABOVE_THRESHOLD_YES);

        $orderId = $unservedValidOrder->getId();
        $caseNumber = $unservedValidOrder->getClient()->getCaseNumber();

        /** @var Client $client */
        $client = $this->getService('test.client');

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/case", [], [], self::BASIC_AUTH_CREDS);
        $caseLink = $crawler->selectLink($caseNumber)->link();
        self::assertContains("/order/${orderId}/summary", $caseLink->getUri());

    }
}
