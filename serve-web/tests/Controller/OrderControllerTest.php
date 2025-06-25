<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Order;
use App\TestHelpers\FileTestHelper;
use App\Tests\ApiWebTestCase;
use Behat\Mink\Driver\Goutte\Client as HTTPClient;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends ApiWebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testProcessOrderDocCaseNumberMismatch()
    {
        $order = $this->createOrder(Order::TYPE_HW);

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - WRONGCASENO.docx',
            'validCO - WRONGCASENO.docx',
            'application/msword'
        );

        /** @var HTTPClient $client */
        $client = ApiWebTestCase::getService('test.client');
        $orderId = $order->getId();

        $client->request(Request::METHOD_POST, "/order/$orderId/process-order-doc", [], ['court-order' => $file], self::BASIC_AUTH_CREDS);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    /** @dataProvider acceptedDocTypesProvider */
    public function testProcessOrderDocAcceptedFilesNotWord($fileLocation, $originalName, $mimeType)
    {
        $order = $this->createOrder(Order::TYPE_HW);

        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);

        /** @var HTTPClient $client */
        $client = ApiWebTestCase::getService('test.client');
        $orderId = $order->getId();

        $client->request(Request::METHOD_POST, "/order/$orderId/process-order-doc", [], ['court-order' => $file], self::BASIC_AUTH_CREDS);

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

    /**
     * @dataProvider partialExtractionProvider
     *
     * @param string $orderType , the type of Order (HW or PF)
     * @param string $fileName  , the fixture filename to load
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testProcessOrderDocPartialExtraction(string $orderType, string $fileName)
    {
        $order = $this->createOrder($orderType);

        $file = FileTestHelper::createUploadedFile(
            "/tests/TestData/$fileName",
            $fileName,
            'application/msword'
        );

        /** @var HTTPClient $client */
        $client = ApiWebTestCase::getService('test.client');
        $orderId = $order->getId();

        $client->request(Request::METHOD_POST, "/order/$orderId/process-order-doc", [], ['court-order' => $file], self::BASIC_AUTH_CREDS);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }

    public function partialExtractionProvider()
    {
        return [
            'Missing SubType' => [
                'HW',
                'Missing sub type - 93559316.docx',
            ],
            'Missing AppointmentType' => [
                'HW',
                'Missing appointment type - 93559316.docx',
            ],
            'Missing HasAssetsAboveThreshold' => [
                'PF',
                'Missing bond amount - 93559316.docx',
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
        $orderType,
    ) {
        $order = $this->createOrder($orderType);
        $order->setSubType($subTypeValue);
        $order->setAppointmentType($appointmentTypeValue);
        $order->setHasAssetsAboveThreshold($hasAssetsAboveThresholdValue);
        $this->persistEntity($order);

        /** @var HTTPClient $client */
        $client = ApiWebTestCase::getService('test.client');
        $orderId = $order->getId();

        $client->request(Request::METHOD_POST, "/order/$orderId/confirm-order-details", [], [], self::BASIC_AUTH_CREDS);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        foreach ($missingElementIds as $id) {
            self::assertStringNotContainsString($id, $client->getResponse()->getContent());
        }

        self::assertStringContainsString($visibleElementId, $client->getResponse()->getContent());
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
                'HW',
            ],
            'appointmentType not extracted' => [
                Order::SUBTYPE_NEW,
                null,
                null,
                ['confirm_order_details_form_subType'],
                'confirm_order_details_form_appointmentType',
                'HW',
            ],
            'hasAssetsAboveThreshold not extracted' => [
                Order::SUBTYPE_NEW,
                Order::APPOINTMENT_TYPE_JOINT,
                null,
                ['confirm_order_details_form_appointmentType, confirm_order_details_form_subType'],
                'confirm_order_details_form_hasAssetsAboveThreshold',
                'PF',
            ],
        ];
    }

    public function testConfirmOrderDetailsValidOrder()
    {
        $order = $this->createOrder('PF');
        $order->setSubType(Order::SUBTYPE_NEW);
        $order->setAppointmentType(Order::APPOINTMENT_TYPE_JOINT);
        $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_YES);
        $this->persistEntity($order);

        /** @var HTTPClient $client */
        $client = ApiWebTestCase::getService('test.client');
        $orderId = $order->getId();

        $client->request(Request::METHOD_POST, "/order/$orderId/confirm-order-details", [], [], self::BASIC_AUTH_CREDS);

        self::assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        self::assertEquals("/order/$orderId/summary", $client->getResponse()->headers->get('location'));
    }
}
