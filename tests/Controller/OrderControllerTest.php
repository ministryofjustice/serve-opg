<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Order;
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
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $order->setSubType('NEW_ORDER');
        $order->setAppointmentType('JOINT_AND_SEVERAL');

        $this->orderService->hydrateOrderFromDocument(
            Argument::type(UploadedFile::class),
            Argument::type(Order::class)
        )->shouldBeCalled()->willReturn($order);


        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO.docx', 'validCO.docx', 'application/msword');
        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, '12345');

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
