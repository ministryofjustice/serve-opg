<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Repository\DocumentRepository;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

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
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * DocumentService constructor.
     * @param StorageInterface $s3Storage
     * @param LoggerInterface $logger
     * @param DocumentRepository $documentRepository
     */
    public function __construct(StorageInterface $s3Storage, LoggerInterface $logger, DocumentRepository $documentRepository)
    {
        $this->storage = $s3Storage;
        $this->logger = $logger;
        $this->documentRepository = $documentRepository;
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

    public function documentLikelyValid(int $documentId, Client $client)
    {
        /** @var Document $document */
        $document = $this->documentRepository->findById($documentId);
        $fileName = $document->getFileName();

        return $this->fileNameContainsClientName($fileName, $client->getClientName()) &&
            $this->fileNameContainsDocumentType($fileName, $document->getType());
    }

    private function fileNameContainsClientName(string $fileName, string $clientName)
    {
        return strpos($fileName, $clientName) !== false;
    }

    private function fileNameContainsDocumentType(string $fileName, ?string $getType)
    {
        return strpos($fileName, $getType) !== false;

    }
}
