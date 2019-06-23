<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Order;
use App\Service\File\Checker\Exception\InvalidFileTypeException;
use App\Service\File\Checker\Exception\RiskyFileException;
use App\Service\File\Checker\Exception\VirusFoundException;
use App\Service\File\Checker\FileCheckerFactory;
use App\Service\File\FileUploader;
use App\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentService
{
    const SUCCESS = 1;
    const FAIL = 0;
    const ERROR = 2;

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
     * @var FileCheckerFactory
     */
    private $fileCheckerFactory;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var bool
     */
    private $debugModeEnabled;

    /**
     * DocumentService constructor.
     * @param EntityManager $em
     * @param StorageInterface $s3Storage
     * @param LoggerInterface $logger
     * @param FileCheckerFactory $fileCheckerFactory
     * @param FileUploader $fileUploader
     * @param TranslatorInterface $translator
     * @param bool $debugModeEnabled
     */
    public function __construct(
        EntityManager $em,
        StorageInterface $s3Storage,
        LoggerInterface $logger,
        FileCheckerFactory $fileCheckerFactory,
        FileUploader $fileUploader,
        TranslatorInterface $translator,
        bool $debugModeEnabled
    ) {
        $this->em = $em;
        $this->storage = $s3Storage;
        $this->logger = $logger;
        $this->fileCheckerFactory = $fileCheckerFactory;
        $this->fileUploader = $fileUploader;
        $this->translator = $translator;
        $this->debugModeEnabled = $debugModeEnabled;
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

    public function processDocument(
        Order $order,
        Document $document,
        UploadedFile $file,
        string $requestId
    ) {
        $response = array(
            'response' => self::FAIL,
            'message' => '',
        );

        try {
            $fileObject = $this->fileCheckerFactory->factory($file);

            $fileObject->checkFile();
            if ($fileObject->isSafe()) {
                $document = $this->fileUploader->uploadFile(
                    $order,
                    $document,
                    $file
                );

                $fileName = $file->getClientOriginalName();
                $document->setFilename($fileName);

                $this->em->persist($document);
                $this->em->flush($document);

                $response["response"] = self::SUCCESS;
                $response["id"] = $document->getId();
                $response["message"] = 'File uploaded';
            } else {
                $response["message"] = 'File could not be uploaded';
            }

        } catch (\Exception $e) {
            $errorToErrorTranslationKey = [
                InvalidFileTypeException::class => 'notSupported',
                RiskyFileException::class => 'risky',
                VirusFoundException::class => 'virusFound',
            ];

            $errorKey = isset($errorToErrorTranslationKey[get_class($e)]) ?
                $errorToErrorTranslationKey[get_class($e)] : 'generic';

            $message = $this->translator->trans("document.file.errors.{$errorKey}", [
                '%techDetails%' => $this->debugModeEnabled ? $e->getMessage() : $requestId,
            ], 'validators');

            $this->logger->error($e->getMessage());

            $response["response"] = self::ERROR;
            $response["message"] = $message;
        }

        return $response;
    }


}
