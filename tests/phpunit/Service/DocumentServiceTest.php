<?php declare(strict_types=1);

namespace tests\phpunit\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\OrderHw;
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
    public function testDocumentLikelyValid(Document $document, $expectedValidationResult, Client $client)
    {
        /** @var EntityManager|ObjectProphecy $em */
        $em = $this->prophesize(EntityManager::class);
        $storage = $this->prophesize(S3Storage::class);
        $logger = new Logger('logger');

        $sut = new DocumentService($storage->reveal(), $logger, $em->reveal());

        self::assertEquals($expectedValidationResult, $sut->documentLikelyValid($document->getFileName(), $document->getType(), $client->getClientName()));
    }

    public function documentProvider()
    {
        $client = new Client('123455678', 'Firstname Lastname', new DateTime());
        $order = new OrderHw($client, new DateTime('-1 days'), new DateTime());

        $validExactMatch = new Document($order, Document::TYPE_COP1A);
        $validExactMatch->setFileName('LASTNAME COP1A 001.tif');

        $validUppercase = new Document($order, Document::TYPE_COP1A);
        $validUppercase->setFileName('LASTNAME COP1A 001.tif');

        $validMixedCase = new Document($order, Document::TYPE_COP1A);
        $validMixedCase->setFileName('LaSTnaME COP1A 001.tif');

        $validLowerCase = new Document($order, Document::TYPE_COP1A);
        $validLowerCase->setFileName('lastname COP1A 001.tif');

        $invalidDocument = new Document($order, Document::TYPE_COP1A);
        $invalidDocument->setFileName('WRONGLASTNAME NOTADOCTYPE 001.tif');

        return [
            'valid - exact match' => [$validExactMatch, true, $client],
            'valid - uppercase name' => [$validUppercase, true, $client],
            'valid - mixed case name' => [$validMixedCase, true, $client],
            'valid - lower case name' => [$validLowerCase, true, $client],
            'invalid document' => [$invalidDocument, false, $client],
        ];
    }
}
