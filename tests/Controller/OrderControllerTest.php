<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Document;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\TimeService;
use App\Tests\ApiWebTestCase;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use DateTime;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends ApiWebTestCase
{
    /**
     * @var OrderController
     */
    private $sut;

    public static function setUpBeforeClass()
    {
        ClockMock::register(TimeService::class);
    }

    public function setUp()
    {
        parent::setUp();
        /** @var OrderController sut */
        $this->sut = $this->getService('App\Controller\OrderController');
    }

    protected function timeTravel(string $dateTime)
    {
        ClockMock::withClockMock(strtotime($dateTime));
    }

    public function testProcessOrderDocSuccess()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', Order::TYPE_HW);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - 93559316.docx',
            'validCO - 93559316.docx',
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();

        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file]);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }

    public function testProcessOrderDocCaseNumberMismatch()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345', Order::TYPE_HW);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - WRONGCASENO.docx',
            'validCO - WRONGCASENO.docx',
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file]);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    /** @dataProvider acceptedDocTypesProvider */
    public function testProcessOrderDocAcceptedFilesNotWord($fileLocation, $originalName, $mimeType)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345', Order::TYPE_HW);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file]);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }

    public function acceptedDocTypesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/test.jpg', 'test.jpg', 'image/jpeg'],
            'jpeg' => ['/tests/TestData/test.jpeg', 'test.jpeg', 'image/jpeg'],
            'pdf' => ['/tests/TestData/test.pdf', 'test.pdf', 'application/pdf'],
            'tiff' => ['/tests/TestData/test.tiff', 'test.tiff', 'image/tiff'],
        ];
    }

    /** @dataProvider partialExtractionProvider
     * @param string $orderType , the type of Order (HW or PF)
     * @param string $fileName , the fixture filename to load
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testProcessOrderDocPartialExtraction(string $orderType, string $fileName)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        $file = FileTestHelper::createUploadedFile(
            "/tests/TestData/${fileName}",
            $fileName,
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file]);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }

    public function partialExtractionProvider()
    {
        return [
            'Missing SubType' => [
                'HW',
                'Missing sub type - 93559316.docx'
            ],
            'Missing AppointmentType' => [
                'HW',
                'Missing appointment type - 93559316.docx'
            ],
            'Missing HasAssetsAboveThreshold' => [
                'PF',
                'Missing bond amount - 93559316.docx'
            ],
        ];
    }

    /** @dataProvider dataExtractionResultsProvider */
    public function testConfirmOrderDetailsDataExtractionResults(
        $subTypeValue,
        $appointmentTypeValue,
        $hasAssetsAboveThresholdValue,
        $missingElementIds,
        $visibleElementId,
        $orderType
    ) {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345', $orderType);
        $order->setSubType($subTypeValue);
        $order->setAppointmentType($appointmentTypeValue);
        $order->setHasAssetsAboveThreshold($hasAssetsAboveThresholdValue);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/confirm-order-details", [], []);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        foreach($missingElementIds as $id) {
            self::assertNotContains($id, $client->getResponse()->getContent());
        }

        self::assertContains($visibleElementId, $client->getResponse()->getContent());
    }

    public function dataExtractionResultsProvider()
    {
        return [
            'subType not extracted' => [
                null,
                Order::APPOINTMENT_TYPE_JOINT,
                null,
                ['confirm_order_details_form_appointmentType'],
                'confirm_order_details_form_subType',
                'HW'
            ],
            'appointmentType not extracted' => [
                Order::SUBTYPE_NEW,
                null,
                null,
                ['confirm_order_details_form_subType'],
                'confirm_order_details_form_appointmentType',
                'HW'
            ],
            'hasAssetsAboveThreshold not extracted' => [
                Order::SUBTYPE_NEW,
                Order::APPOINTMENT_TYPE_JOINT,
                null,
                ['confirm_order_details_form_appointmentType, confirm_order_details_form_subType'],
                'confirm_order_details_form_hasAssetsAboveThreshold',
                'PF'
            ],
        ];
    }

    public function testConfirmOrderDetailsValidOrder()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345', OrderPf::TYPE_PF);
        $order->setSubType(Order::SUBTYPE_NEW);
        $order->setAppointmentType(Order::APPOINTMENT_TYPE_JOINT);
        $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_YES);

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/confirm-order-details", [], []);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertEquals("/order/${orderId}/summary", $client->getResponse()->headers->get('location'));
    }

    /**
     * @group time-sensitive
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testValidCasesCreatedBeforeOrderUploadFeatureWithoutWordOrderRedirectsToUploadPage()
    {
        // Change the time for this test to be BEFORE the feature release date so the order created date
        // is prior to feature release date
        $featureReleaseDate = new DateTime(self::$container->getParameter('coUploadReleaseDate'));
        $oneDayBeforeFeatureRelease = $featureReleaseDate->modify('-1 day');
        $this->timeTravel($oneDayBeforeFeatureRelease->format('Y-m-d'));

        $unservedValidOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '12345678', OrderPf::TYPE_PF);
        $unservedValidOrder->setSubType(OrderPf::SUBTYPE_NEW);
        $unservedValidOrder->setAppointmentType(OrderPf::APPOINTMENT_TYPE_JOINT);
        $unservedValidOrder->setHasAssetsAboveThreshold(OrderPf::HAS_ASSETS_ABOVE_THRESHOLD_YES);

        $em = $this->getEntityManager();
        $em->persist($unservedValidOrder);

        $file = FileTestHelper::createUploadedFile('/tests/TestData/test.tiff', 'test.tiff', 'image/tiff');
        $document = new Document($unservedValidOrder, Document::TYPE_COURT_ORDER);
        $document->setFile($file);
        $document->setFileName('test.tiff');
        $document->setStorageReference('some-storage-reference.com/test.tiff');
        $document->setRemoteStorageReference('some-remote-storage-reference.com/test.tiff');

        $em->persist($document);
        $em->flush();

        /** @var Client $client */
        $client = $this->createAuthenticatedClient();

        $orderId = $unservedValidOrder->getId();

        /** @var Crawler $crawler */
        $client->request(Request::METHOD_GET, "/order/${orderId}/summary", [], []);
        $crawler = $client->followRedirect();

        self::assertContains("/order/${orderId}/upload", $crawler->getUri());
    }
}
