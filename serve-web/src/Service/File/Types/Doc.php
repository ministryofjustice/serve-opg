<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\DocChecker;
use Psr\Log\LoggerInterface;

class Doc extends UploadableFile
{
    protected $scannerEndpoint = 'upload/doc';

    public function __construct(
        ClamAVChecker $virusChecker,
        DocChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
