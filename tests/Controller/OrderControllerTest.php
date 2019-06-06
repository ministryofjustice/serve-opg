<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Service\DocumentService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class OrderControllerTest extends TestCase
{
    public function setUp()
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->orderService = $this->prophesize(OrderService::class);
        /** @var DocumentService|ObjectProphecy $documentService */
        $this->documentService = $this->prophesize(DocumentService::class);
    }

    /**
     * @group acs
     */
    public function testStep1Process()
    {
        $sut = new OrderController(
            $this->em->reveal(),
            $this->orderService->reveal(),
            $this->documentService->reveal()
        );

        $fileLocation = __DIR__ . '/validCO.docx';
        $file = new UploadedFile($fileLocation, 'validCO.docx', 'application/msword', null);
        $expectedJSONResponse = json_encode(['valid' => true]);

        $request = new Request([], [], [], [], ['court-order' => $file]);

        $response = $sut->step1Process($request);

        self::assertEquals($expectedJSONResponse, $response->getContent());
    }
}
