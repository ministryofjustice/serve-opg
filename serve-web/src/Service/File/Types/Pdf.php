<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\PdfChecker;
use Psr\Log\LoggerInterface;

class Pdf extends UploadableFile
{
    protected $scannerEndpoint = 'upload/pdf';

    public function __construct(
        ClamAVChecker $virusChecker,
        PdfChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
