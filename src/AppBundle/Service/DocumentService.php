<?php

namespace AppBundle\Service;

use AppBundle\Entity\Document;
use AppBundle\Service\File\Storage\StorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DocumentService
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * DocumentService constructor.
     * @param StorageInterface $s3Storage
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     */
    public function __construct(StorageInterface $s3Storage, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->storage = $s3Storage;
        $this->logger = $logger;
        $this->em = $em;
    }

    // @todo refactor into DocumentRepository, remove $deleteFromS3 and call separately where required
    public function deleteDocumentById($id)
    {
        /** @var Document $document */
        $document = $this->em->find(Document::class, $id);

        if (!$document instanceof Document) {
            throw new \RuntimeException("document not found");
        }

        $this->deleteFromS3($document);

        $this->em->remove($document);
        $this->em->flush($document);
    }

    // @todo can be refactored out in to generic S3 service
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

    public function documentLikelyValid(string $fileName, string $documentType, string $clientName)
    {
        return $this->nameIsPresent($fileName, $clientName) && stripos($fileName, $documentType) !== false;
    }

    public function clientNameIsValidInFilename($fileName, $clientName)
    {
        $names = explode(' ', $clientName);

        foreach ($names as $name) {
            if (stripos($fileName, $name) !== false) {
                return true;
            }
        }

        return false;
    }

    public function docTypeIsValidInFilename($fileName, $docType)
    {
        return stripos($fileName, $docType) !== false;
    }
}
