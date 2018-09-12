<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Order;
use AppBundle\Entity\Document;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;

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
     * @var array
     */
    private $options;

    /**
     * FileUploader constructor.
     */
    public function __construct(StorageInterface $s3Storage, LoggerInterface $logger, array $options = [])
    {
        $this->storage = $s3Storage;
        $this->logger = $logger;
        $this->fileCheckers = [];
        $this->options = [];
    }

    /**
     * Uploads a file into S3 + create and persist a Document entity using that reference
     *
     * @param ReportInterface $reportId
     * @param string          $body
     *
     * @return Document
     */
    public function uploadFile(Order $order, $body)
    {
        // @to-do move call to storage reference outside fileUploader - to decouple.
        $storageReference = $this->generateStorageReference($order);

        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploder : stored $storageReference, " . strlen($body) . ' bytes');

        $document = new Document($order, $order->getType());
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
    public function generateStorageReference($entity)
    {
        if (is_object($entity) && method_exists($entity, 'getId') && is_numeric($entity->getId())) {
            return 'dc_doc_' . $entity->getId() . '_' . str_replace('.', '', microtime(1));
        }
        throw new \RuntimeException('Unable to generate storage reference, entity provided does not have an ID');
    }
}
