<?php

namespace App\Service;

use App\Entity\Document;
use App\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

class DocumentService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DocumentService constructor.
     * @param EntityManager $em
     * @param StorageInterface $s3Storage
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, StorageInterface $s3Storage, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->storage = $s3Storage;
        $this->logger = $logger;
    }

    public function deleteDocumentById($id)
    {
        /** @var Document $document */
        $document = $this->em->getRepository(Document::class)->find($id);
        if (!$document instanceof Document) {
            throw new \RuntimeException("document not found");
        }

        $this->deleteFromS3($document);

        $this->em->remove($document);
        $this->em->flush($document);
    }

    /**
     * @param  Document   $document
     * @param  bool       $ignoreS3Failure
     * @throws \Exception if the document doesn't exist (in addition to S3 network/access failures
     * @return bool       true if delete is successful
     *
     */
    private function deleteFromS3(Document $document, $ignoreS3Failure = false)
    {
        $ref = $document->getStorageReference();
        if (!$ref) {
            $this->logger->notice('empty file reference for document ' . $document->getId() . ", can't delete");
            return true;
        }

        try {
            $this->logger->notice("Deleting $ref from S3");
            $return = $this->storage->delete($ref);
            $this->logger->notice('RETURNED->>> ' . $return);
            $this->logger->notice("Deleting for $ref from S3: no exception thrown from deleteObject operation");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("deleting $ref from S3: exception (" . ($ignoreS3Failure ? '(ignored)' : '') . ' ' . $e->getMessage());
            if (!$ignoreS3Failure) {
                throw $e;
            }
        }
    }
}
