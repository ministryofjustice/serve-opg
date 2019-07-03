<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\CaseController;
use App\Entity\Document;
use App\Entity\OrderPf;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use DateTime;
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
        $unservedNotValidOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345678', OrderPf::TYPE_PF);

        $em = $this->getEntityManager();
        $em->persist($unservedNotValidOrder);
        $em->flush();

        $orderId = $unservedNotValidOrder->getId();
        $caseNumber = $unservedNotValidOrder->getClient()->getCaseNumber();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/case", [], []);
        $caseLink = $crawler->selectLink($caseNumber)->link();

        self::assertContains("/order/${orderId}/upload", $caseLink->getUri());
    }

    public function testCaseLinkForUnservedValidCasesLinksToSummaryView()
    {
        $unservedValidOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345678', OrderPf::TYPE_PF);
        $unservedValidOrder->setSubType(OrderPf::SUBTYPE_NEW);
        $unservedValidOrder->setAppointmentType(OrderPf::APPOINTMENT_TYPE_JOINT);
        $unservedValidOrder->setHasAssetsAboveThreshold(OrderPf::HAS_ASSETS_ABOVE_THRESHOLD_YES);

        $em = $this->getEntityManager();
        $em->persist($unservedValidOrder);
        $em->flush();

        $orderId = $unservedValidOrder->getId();
        $caseNumber = $unservedValidOrder->getClient()->getCaseNumber();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_GET, "/case", [], []);
        $caseLink = $crawler->selectLink($caseNumber)->link();

        self::assertContains("/order/${orderId}/summary", $caseLink->getUri());
    }
}
