<?php declare(strict_types=1);

namespace tests\phpunit\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\OrderHw;
use AppBundle\Repository\DocumentRepository;
use AppBundle\Service\DocumentService;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class DocumentServiceTest extends TestCase
{
    /**
     * @dataProvider documentProvider
     */
    public function testDocumentLikelyValid($document, $validationResult, $client)
    {
        /** @var EntityManager|ObjectProphecy $em */
        $em = $this->prophesize(EntityManager::class);
        $storage = $this->prophesize(S3Storage::class);
        $logger = new Logger('logger');

        $sut = new DocumentService($storage->reveal(), $logger, $em->reveal());

        self::assertEquals($validationResult, $sut->documentLikelyValid($document->getFileName(), $document->getType(), $client->getClientName()));
    }

    public function documentProvider()
    {
        $client = new Client('123455678', 'client name', new DateTime());
        $order = new OrderHw($client, new DateTime('-1 days'), new DateTime());

        $validDocument = new Document($order, Document::TYPE_COP1A);
        $validDocument->setFileName('client name COP1A');
        $validDocument->setId(1);

        $invalidDocument = new Document($order, Document::TYPE_COP1A);
        $invalidDocument->setFileName('wrong identifier NOTADOCTYPE');
        $invalidDocument->setId(2);

        return [
            'valid document' => [$validDocument, true, $client],
            'invalid document' => [$invalidDocument, false, $client],
        ];
    }
}
