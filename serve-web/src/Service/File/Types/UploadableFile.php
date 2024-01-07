<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\FileCheckerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableFile implements UploadableFileInterface
{
    protected string $scannerEndpoint = 'UNDEFINED';

    /**
     * @var FileCheckerInterface[]
     */
    protected array $fileCheckers;

    protected LoggerInterface $logger;

    protected UploadedFile $uploadedFile;

    protected array $scanResult;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return FileCheckerInterface[]
     */
    public function getFileCheckers(): array
    {
        return $this->fileCheckers;
    }

    /**
     * @param FileCheckerInterface[] $fileCheckers
     */
    public function setFileCheckers(array $fileCheckers): static
    {
        $this->fileCheckers = $fileCheckers;
        return $this;
    }

    public function getLogger(): loggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(UploadedFile $uploadedFile): static
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws Exception
     */
    public function checkFile(): void
    {
        $this->callFileCheckers();
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws Exception
     */
    public function callFileCheckers(): void
    {
        foreach ($this->getFileCheckers() as $fc) {
            // send file
            $fc->checkFile($this);
        }
    }

    public function getScanResult(): array
    {
        return $this->scanResult;
    }

    public function setScanResult(array $scanResult): static
    {
        $this->scanResult = $scanResult;
        return $this;
    }

    public function isSafe(): bool
    {
        /**** TO DO REMOVE THIS ONCE FILE SCANNER IMPLEMENTED *****/
        /**** TO DO 2024 *****/
        return true;
        $scanResult = $this->getScanResult();

        if (isset($scanResult['file_scanner_result']) && strtoupper($scanResult['file_scanner_result'] == 'PASS')) {
            return true;
        }

        return false;
    }

    public function getScannerEndpoint(): string
    {
        return $this->scannerEndpoint;
    }
}
