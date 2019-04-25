<?php

namespace App\Service\File;

use App\Entity\Order;
use App\Entity\Document;
use App\Service\File\Storage\StorageInterface;
use App\Service\File\Types\UploadableFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FileUploader constructor.
     * @param StorageInterface $storage
     * @param LoggerInterface $logger
     */
    public function __construct(StorageInterface $storage, LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference
     *
     * @param Order $order
     * @param Document $document
     * @param UploadedFile $uploadedFile
     * @return Document
     */
    public function uploadFile(Order $order, Document $document, UploadedFile $uploadedFile)
    {
        // @to-do move call to storage reference outside fileUploader - to decouple.
        $storageReference = $this->generateStorageReference($uploadedFile, $order);

        $body = file_get_contents($uploadedFile->getPathName());
        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploader : stored $storageReference, " . $uploadedFile->getSize() . ' bytes');

        $document->setStorageReference($storageReference);

        return $document;
    }

    /**
     * Generates a storage reference to reduce the coupling of fileuploader to either Order or
     * Report entities.
     *
     * @param $entity Object doctrine entity that has an id field
     *
     * @return string
     */
    public function generateStorageReference(UploadedFile $uploadedFile, $entity)
    {
        if (is_object($entity) && method_exists($entity, 'getId') && is_numeric($entity->getId())) {
            return 'dc_doc_' . $entity->getId() . '_' . str_replace('.', '', microtime(1)) . '.' . $uploadedFile->getClientOriginalExtension();
        }
        throw new \RuntimeException('Unable to generate storage reference, entity provided does not have an ID');
    }
}
