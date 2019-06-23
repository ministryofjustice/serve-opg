<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Order;
use App\exceptions\WrongCaseNumberException;
use App\Service\DocumentService;
use App\Service\OrderService;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    /**
     * @var EntityManager|ObjectProphecy
     */
    private $em;

    /**
     * @var OrderService|ObjectProphecy
     */
    private $orderService;

    /**
     * @var DocumentService|ObjectProphecy
     */
    private $documentService;


    public function setUp()
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->orderService = $this->prophesize(OrderService::class);
        $this->documentService = $this->prophesize(DocumentService::class);
    }

    public function testProcessOrderDocSuccess()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $order->setSubType('NEW_APPLICATION');
        $order->setAppointmentType('JOINT_AND_SEVERAL');

        $this->orderService->hydrateOrderFromDocument(
            Argument::type(UploadedFile::class),
            Argument::type(Order::class)
        )->shouldBeCalled()->willReturn($order);

        $this->em->persist($order)->shouldBeCalled();
        $this->em->flush($order)->shouldBeCalled();

        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO - 93559316.docx', 'validCO - 93559316.docx', 'application/msword');
        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testProcessOrderDocCaseNumberMismatch()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $this->orderService->hydrateOrderFromDocument(
            Argument::type(UploadedFile::class),
            Argument::type(Order::class)
        )->shouldBeCalled()->willThrow(new WrongCaseNumberException());

        $this->em->persist($order)->shouldNotBeCalled();
        $this->em->flush($order)->shouldNotBeCalled();

        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - WRONGCASENO.docx',
            'validCO - WRONGCASENO.docx',
            'application/msword'
        );

        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /** @dataProvider acceptedDocTypesProvider */
    public function testProcessOrderDocAcceptedFilesNotWord($fileLocation, $originalName, $mimeType)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);
        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('Document is not in .doc or .docx format', $response->getContent());
    }

    private function acceptedDocTypesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/test.jpg', 'test.jpg', 'image/jpeg'],
            'jpeg' => ['/tests/TestData/test.jpeg', 'test.jpeg', 'image/jpeg'],
            'pdf' => ['/tests/TestData/test.pdf', 'test.pdf', 'application/pdf'],
            'tiff' => ['/tests/TestData/test.tiff', 'test.tiff', 'image/tiff'],
        ];
    }

    /** @dataProvider partialExtractionProvider
     * @param string|null $appointmentType , the value of appointmentType
     * @param string|null $subType , the value of subType
     * @param string|null $hasAssetsAboveThreshold
     * @param string $orderType, the type of Order (HW or PF)
     * @throws WrongCaseNumberException
     * @throws \App\exceptions\NoMatchesFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testProcessOrderDocPartialExtraction(
        ?string $appointmentType,
        ?string $subType,
        ?string $hasAssetsAboveThreshold,
        string $orderType
    ) {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $order->setSubType($subType);
        $order->setAppointmentType($appointmentType);
        $order->setHasAssetsAboveThreshold($hasAssetsAboveThreshold);

        $this->orderService->hydrateOrderFromDocument(
            Argument::type(UploadedFile::class),
            Argument::type(Order::class)
        )->shouldBeCalled()->willReturn($order);

        $this->em->persist($order)->shouldBeCalled();
        $this->em->flush($order)->shouldBeCalled();

        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/Missing appointment type - 93559316.docx',
            'Missing appointment type - 93559316.docx',
            'application/msword'
        );

        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('partial data extraction', $response->getContent());
    }

    public function partialExtractionProvider()
    {
        return [
            'Missing SubType' => [
                'JOINT_AND_SEVERAL',
                null,
                null,
                'HW'
            ],
            'Missing AppointmentType' => [
                null,
                'NEW_APPLICATION',
                null,
                'HW'
            ],
            'Missing HasAssetsAboveThreshold' => [
                'JOINT_AND_SEVERAL',
                'NEW_APPLICATION',
                null,
                'PF'
            ],
        ];
    }

//    public function testEditOrderDataExtractionResults($formValues, $missingElementIds, $visibleElementId)
//    {
//        $sut = new OrderController(
//            $this->em->reveal(),
//            $this->orderService->reveal(),
//            $this->documentService->reveal()
//        );
//
//        $request = new Request([], $formValues, [], [], []);
//
//        /** @var Response $response */
//        $response = $sut->editAction($request, 12345);
//
//        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
//
//        foreach($missingElementIds as $id) {
//            self::assertNotContains($id, $response->getContent());
//        }
//
//        self::assertContains($visibleElementId, $response->getContent());
//    }
//
//    public function dataExtractionFormValuesProvider()
//    {
//        return [
//            'subType not extracted' => [
//                ['subTypeExtracted' => false, 'appointmentTypeExtracted' => true],
//                ['order_form_appointmentType'],
//                'order_form_subType'
//            ],
//            'appointmentType not extracted' => [
//                ['subTypeExtracted' => true, 'appointmentTypeExtracted' => false],
//                ['order_form_subType'],
//                'order_form_subType'
//            ],
//            'hasAssetsAboveThreshold not extracted' => [
//                ['subTypeExtracted' => true, 'appointmentTypeExtracted' => true, 'hasAssetsAboveThresholdExtracted' => false],
//                ['order_form_appointmentType, order_form_subType'],
//                'order_form_hasAssetsAboveThreshold'
//            ],
//        ];
//    }
}
