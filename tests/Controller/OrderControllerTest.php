<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\DocumentService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OrderControllerTest extends WebTestCase
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
//        $client = new Client('CASENUMBER', 'Client Name', new DateTime());
//        $order = new OrderHw($client, new DateTime(), new DateTime());
//        $document = new Document($order, 'COURT_ORDER');

        $fileLocation = __DIR__ . '/validCO.docx';
        $file = new UploadedFile($fileLocation, 'validCO.docx', 'application/msword', null);
        $expectedJSONResponse = json_encode(['valid' => true]);
        $this->documentService->processFile($file)->shouldBeCalled()->willReturn($expectedJSONResponse);

        $symfonyClient = static::createClient();
        $symfonyClient->request(
            'POST',
            '/order/1/step-1-process',
            [],
            ['court_order' => $file],
            // Simulate authenticating
            ['PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word',]
            );

        self::assertTrue($symfonyClient->getResponse()->isSuccessful());
        self::assertEquals($symfonyClient->getResponse()->getTargetUrl(), '/order/1/summary');
    }
}
