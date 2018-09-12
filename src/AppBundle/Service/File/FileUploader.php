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
     * @param string          $fileName
     *
     * @return Document
     */
    public function uploadFile(Order $order, $body, $fileName)
    {
        $orderId = $order->getId();
        $storageReference = 'dd_doc_' . $orderId . '_' . str_replace('.', '', microtime(1));

        $this->storage->store($storageReference, $body);
        $this->logger->debug("FileUploder : stored $storageReference, " . strlen($body) . ' bytes');

        $document = new Document();
        $document
            ->setStorageReference($storageReference)
            ->setFileName($fileName);

//        $reportType = $report instanceof Report ? 'report' : 'ndr';
//        $ret = $this->restClient->post("/document/{$reportType}/{$orderId}", $document, ['document']);
//        $document->setId($ret['id']);

        return $document;
    }
}
