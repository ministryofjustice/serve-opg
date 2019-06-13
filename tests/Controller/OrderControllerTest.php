<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Service\DocumentService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class OrderControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->orderService = $this->prophesize(OrderService::class);
        /** @var DocumentService|ObjectProphecy $documentService */
        $this->documentService = $this->prophesize(DocumentService::class);
    }

    public function testAssertDocType()
    {
        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
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
}
