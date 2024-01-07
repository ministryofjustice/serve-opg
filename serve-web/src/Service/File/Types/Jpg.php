<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\JpgChecker;
use Psr\Log\LoggerInterface;

class Jpg extends UploadableFile
{
    protected string $scannerEndpoint = 'upload/jpeg';

    public function __construct(
        ClamAVChecker $virusChecker,
        JpgChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->setFileCheckers([$virusChecker, $fileChecker]);
    }
}
