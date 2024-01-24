<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\PngChecker;
use Psr\Log\LoggerInterface;

class Png extends UploadableFile
{
    protected string $scannerEndpoint = 'upload/png';

    public function __construct(
        ClamAVChecker $virusChecker,
        PngChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->setFileCheckers([$virusChecker, $fileChecker]);
    }
}
