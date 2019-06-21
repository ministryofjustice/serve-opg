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

    public function acceptedDocTypesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/test.jpg', 'test.jpg', 'image/jpeg'],
            'jpeg' => ['/tests/TestData/test.jpeg', 'test.jpeg', 'image/jpeg'],
            'pdf' => ['/tests/TestData/test.pdf', 'test.pdf', 'application/pdf'],
            'tiff' => ['/tests/TestData/test.tiff', 'test.tiff', 'image/tiff'],
        ];
    }

    public function testProcessOrderDocPartialExtractionMissingAppointmentType()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $order->setSubType('NEW_APPLICATION');

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
        self::assertEquals('Partial data extraction', $response->getContent());
    }

    public function testProcessOrderDocPartialExtractionMissingSubType()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

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

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/Missing sub type - 93559316.docx',
            'Missing sub type - 93559316.docx',
            'application/msword'
        );

        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('Partial data extraction', $response->getContent());
    }

    public function testProcessOrderDocPartialExtractionMissingBondAmount()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $order->setAppointmentType('JOINT_AND_SEVERAL');
        $order->setSubType('NEW_APPLICATION');

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
            '/tests/TestData/Missing bond amount - 93559316.docx',
            'Missing sub type - 93559316.docx',
            'application/msword'
        );

        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('Partial data extraction', $response->getContent());
    }
}
