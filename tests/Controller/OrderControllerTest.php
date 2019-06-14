<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\DocumentService;
use App\Service\OrderService;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

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

    /**
     * @var Router|ObjectProphecy
     */
    private $router;


    public function setUp()
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->orderService = $this->prophesize(OrderService::class);
        $this->documentService = $this->prophesize(DocumentService::class);
        $this->router = $this->prophesize(Router::class);
    }

    public function testAssertDocType()
    {
        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal(),
            $this->router->reveal()
        );

        self::bootKernel();
        $container = self::$container;
        $projectDir = $container->get('kernel')->getProjectDir();
        $fileLocation = $projectDir . '/tests/TestData/validCO.docx';

        $file = new UploadedFile($fileLocation, 'validCO.docx', 'application/msword', null);
        $expectedJSONResponse = json_encode(['valid' => true]);

        $request = new Request([], [], [], [], ['court-order' => $file]);

        $response = $sut->assertDocType($request);

        self::assertEquals($expectedJSONResponse, $response->getContent());
    }

    public function testProcessOrderDoc()
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'HW');
        $order->setId(12345);

        $this->orderService->getOrderByIdIfNotServed($order->getId())->shouldBeCalled()->willReturn($order);

        $this->orderService->hydrateOrderFromDocument(
            Argument::type(UploadedFile::class),
            Argument::type(Order::class)
        )->shouldBeCalled()->willReturn(Argument::type(Order::class));

        $this->router->generate('order-summary')->shouldBeCalled()->willReturn('/order/{orderId}/summary');

        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal(),
            $this->router->reveal()
        );

        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO.docx', 'validCO.docx', 'application/msword');
        $request = new Request([], [], [], [], ['court-order' => $file]);

        /** @var Response $response */
        $response = $sut->processOrderDocument($request, 12345);

        self::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }
}
