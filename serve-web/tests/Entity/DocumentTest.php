<?php declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Document;
use App\Entity\OrderPf;
use App\TestHelpers\FileTestHelper;
use App\TestHelpers\OrderTestHelper;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /** @dataProvider docTypesProvider */
    public function testIsWordDocument($fileLocation, $originalName, $mimeType, $expectedResult)
    {
        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);
        $order = OrderTestHelper::generateOrder('2018-01-01', '2018-01-01', '123456', OrderPf::TYPE_PF);
        $document = new Document($order, Document::TYPE_COURT_ORDER);
        $document->setFile($file);
        self::assertEquals($expectedResult, $document->isWordDocument());
    }

    /** @dataProvider docTypesProvider */
    public function testGetMimeType($fileLocation, $originalName, $mimeType)
    {
        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);
        $order = OrderTestHelper::generateOrder('2018-01-01', '2018-01-01', '123456', OrderPf::TYPE_PF);
        $document = new Document($order, Document::TYPE_COURT_ORDER);
        $document->setFile($file);
        self::assertEquals($mimeType, $document->getMimeType());
    }

    public function docTypesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/test.jpg', 'test.jpg', 'image/jpeg', false],
            'tiff' => ['/tests/TestData/test.tiff', 'test.tiff', 'image/tiff', false],
            'pdf' => ['/tests/TestData/test.jpg', 'test.jpg', 'aplication/pdf', false],
            'word' => ['/tests/TestData/test.jpeg', 'test.jpeg', 'application/msword', true],
            'open-word' => ['/tests/TestData/test.pdf', 'test.pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', true]
        ];
    }

    public function testGetMimeTypeNoFile()
    {
        $order = OrderTestHelper::generateOrder('2018-01-01', '2018-01-01', '123456', OrderPf::TYPE_PF);
        $document = new Document($order, Document::TYPE_COURT_ORDER);
        self::assertEquals(null, $document->getMimeType());
    }
}
