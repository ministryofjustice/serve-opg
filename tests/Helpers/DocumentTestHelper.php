<?php declare(strict_types=1);


namespace App\Tests\Helpers;


use App\Entity\Document;
use App\Entity\Order;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentTestHelper
{
    public static function generateDocumentFromFile(UploadedFile $file, Order $order, string $docType)
    {
        $document = new Document($order, $docType);
        $document->setFile($file);
        $document->setFileName($file->getFilename());
        $document->setStorageReference('some-storage-reference.com/' . $file->getFilename());
        $document->setRemoteStorageReference('some-remote-storage-reference.com/' . $file->getFilename());
        return $document;
    }
}