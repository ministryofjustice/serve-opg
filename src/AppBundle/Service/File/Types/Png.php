<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\ClamAVChecker;
use AppBundle\Service\File\Checker\PngChecker;
use Psr\Log\LoggerInterface;

class Png extends UploadableFile
{
    protected $scannerEndpoint = 'upload/png';

    public function __construct(
        ClamAVChecker $virusChecker,
        PngChecker $fileChecker,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
