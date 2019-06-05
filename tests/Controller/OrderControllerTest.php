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
        $fileLocation = __DIR__ . '/validCO.docx';
        $file = new UploadedFile($fileLocation, 'validCO.docx', 'application/msword', null);
        $expectedJSONResponse = json_encode(['valid' => true]);

        $symfonyClient = static::createClient();
        $symfonyClient->request(
            'POST',
            '/order/1/step-1-process',
            [],
            ['court-order' => $file],
            // Use basic auth to skip login redirect
            ['PHP_AUTH_USER' => 'behat@digital.justice.gov.uk', 'PHP_AUTH_PW' => 'Abcd1234',]
        );

        self::assertEquals(200, $symfonyClient->getResponse()->getStatusCode());
        self::assertEquals($expectedJSONResponse, $symfonyClient->getResponse()->getContent());
    }
}
