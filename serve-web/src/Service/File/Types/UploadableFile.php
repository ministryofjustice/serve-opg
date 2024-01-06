<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\FileCheckerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableFile implements UploadableFileInterface
{
    protected string $scannerEndpoint = 'UNDEFINED';

    /**
     * @var FileCheckerInterface[]
     */
    protected $fileCheckers;

    protected LoggerInterface $logger;

    /**
     * @var UploadedFile $file
     */
    protected $uploadedFile;

    /**
     * @var array Scan result
     */
    protected $scanResult;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return FileCheckerInterface[]
     */
    public function getFileCheckers()
    {
        return $this->fileCheckers;
    }

    /**
     * @param FileCheckerInterface[] $fileCheckers
     *
     * @return $this
     */
    public function setFileCheckers($fileCheckers)
    {
        $this->fileCheckers = $fileCheckers;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return $this
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function checkFile(): void
    {
        $this->callFileCheckers();
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function callFileCheckers(): void
    {
        foreach ($this->getFileCheckers() as $fc) {
            // send file
            $fc->checkFile($this);
        }
    }

    /**
     * @return array
     */
    public function getScanResult()
    {
        return $this->scanResult;
    }

    /**
     * @param array $scanResult
     */
    public function setScanResult($scanResult)
    {
        $this->scanResult = $scanResult;
        return $this;
    }

    /**
     * Is the file safe to upload?
     *
     * @return bool
     */
    public function isSafe()
    {
        /**** TO DO REMOVE THIS ONCE FILE SCANNER IMPLEMENTED *****/
        return true;
        $scanResult = $this->getScanResult();

        if (isset($scanResult['file_scanner_result']) && strtoupper($scanResult['file_scanner_result'] == 'PASS')) {
            return true;
        }

        return false;
    }

    public function getScannerEndpoint()
    {
        return $this->scannerEndpoint;
    }
}
